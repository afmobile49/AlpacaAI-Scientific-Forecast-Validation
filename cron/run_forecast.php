<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use MarketForecast\AI\{AiProductionGate, ForecastPairRepository, OpenAIProvider, PairedForecastOrchestrator};
use MarketForecast\Database\Connection;
use MarketForecast\Evaluation\ForecastTargetRepository;
use MarketForecast\Forecast\{ForecastCycle, ForecastFreezer, InformationCutoff, QuantForecastEngine, SnapshotPersistence};
use MarketForecast\Indicators\FeatureSetV1;
use MarketForecast\MarketData\{AlpacaProvider, CompletedMarketBarGuard, MarketCalendarService};

function envValue(string $name, string $file): string
{
    $value = getenv($name);
    if ($value !== false && $value !== '') return $value;
    foreach (@file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        if (!str_contains($line, '=')) continue;
        [$key, $candidate] = explode('=', trim($line), 2);
        if (trim($key) === $name) return trim($candidate, " '\"");
    }
    return '';
}

$database = $argv[1] ?? dirname(__DIR__) . '/database/forecast.sqlite';
$environment = $argv[2] ?? dirname(__DIR__) . '/.env';
$cycle = ForecastCycle::fromArgument($argv[3] ?? 'stock');
$alpacaKey = envValue('ALPACA_API_KEY', $environment);
$alpacaSecret = envValue('ALPACA_API_SECRET', $environment);
$alpacaBase = envValue('ALPACA_DATA_BASE_URL', $environment) ?: 'https://data.alpaca.markets';
if ($alpacaKey === '' || $alpacaSecret === '') throw new RuntimeException('Alpaca credentials are not configured.');

$pdo = Connection::open($database);
$aiFlag = envValue('AI_ENABLED', $environment);
$openAiKey = envValue('OPENAI_API_KEY', $environment);
$aiEnabled = strtolower($aiFlag) === 'true';
if ($aiEnabled) AiProductionGate::assertRuntimeReady($pdo, $aiFlag, $openAiKey);

$pdo->exec("INSERT OR IGNORE INTO experiments(experiment_code,name,description,start_at,status,config_json) VALUES('EXP-001','Daily QUANT/AI forecast','Auditable paired Alpaca forecast',datetime('now'),'ACTIVE','{}')");
$experimentId = (int) $pdo->query("SELECT id FROM experiments WHERE experiment_code='EXP-001'")->fetchColumn();
$marketNow = new DateTimeImmutable('now', new DateTimeZone('America/New_York'));
if ($cycle->assetType() === 'STOCK' && !(new MarketCalendarService())->isTradingSession($marketNow)) {
    throw new RuntimeException('Stock forecast cycle is only allowed on a US trading session.');
}
$runKey = $cycle->runKey($marketNow->format('Y-m-d'));
$existing = $pdo->prepare('SELECT id,status,information_cutoff FROM forecast_runs WHERE run_key=?');
$existing->execute([$runKey]);
$runRow = $existing->fetch(PDO::FETCH_ASSOC);
if ($runRow && $runRow['status'] === 'FINALIZED') {
    echo json_encode(['run_key' => $runKey, 'status' => 'ALREADY_FINALIZED'], JSON_THROW_ON_ERROR) . PHP_EOL;
    exit(0);
}
if ($runRow && $runRow['status'] === 'FAILED') {
    echo json_encode(['run_key' => $runKey, 'status' => 'ALREADY_FAILED'], JSON_THROW_ON_ERROR) . PHP_EOL;
    exit(0);
}

$cutoff = $runRow ? new InformationCutoff(new DateTimeImmutable((string) $runRow['information_cutoff'])) : new InformationCutoff(new DateTimeImmutable('now'));
if (!$runRow) {
    $statement = $pdo->prepare('INSERT INTO forecast_runs(experiment_id,run_key,scheduled_for,started_at,information_cutoff,status,state_detail) VALUES(?,?,?,?,?,?,?)');
    $statement->execute([$experimentId, $runKey, gmdate('c'), gmdate('c'), $cutoff->value(), 'RUNNING', 'CREATED']);
    $runId = (int) $pdo->lastInsertId();
} else {
    $runId = (int) $runRow['id'];
}

$alpaca = new AlpacaProvider($alpacaBase, $alpacaKey, $alpacaSecret);
$guard = new CompletedMarketBarGuard();
$features = new FeatureSetV1();
$snapshots = new SnapshotPersistence($pdo);
$targets = new ForecastTargetRepository($pdo);
$quant = new QuantForecastEngine();
$paired = $aiEnabled ? new PairedForecastOrchestrator(
    $pdo,
    $quant,
    new OpenAIProvider($openAiKey, envValue('OPENAI_MODEL', $environment) ?: 'gpt-5.6'),
    new ForecastPairRepository($pdo),
    new ForecastFreezer()
) : null;
$assetsStatement = $pdo->prepare("SELECT * FROM assets WHERE enabled=1 AND provider='ALPACA' AND asset_type=?");
$assetsStatement->execute([$cycle->assetType()]);
$assets = $assetsStatement->fetchAll(PDO::FETCH_ASSOC);
$created = 0;
$stage = 'START';

try {
    $pdo->beginTransaction();
    foreach ($assets as $asset) {
        $stage = 'MARKET_DATA';
        $bars = $alpaca->getHistoricalBars($asset['provider_symbol'], '1Day', $cutoff->at->modify('-120 days'), $cutoff->at);
        if (count($bars) < 50) continue;
        $stage = 'COMPLETED_BAR_GUARD';
        $last = end($bars);
        $guard->assertAllowed($last, $cutoff->at, $asset['asset_type']);
        $stage = 'SNAPSHOT_PERSIST';
        $featureSet = $features->calculate($bars);
        $featureSet['close'] = (float) $last['close'];
        $snapshot = $snapshots->save($experimentId, (int) $asset['id'], $cutoff->value(), $featureSet, $bars);
        $horizons = $asset['asset_type'] === 'STOCK' ? ['1D', '5D', '20D'] : ['24H', '7D', '30D'];
        foreach ($horizons as $horizon) {
            try {
                if (!$paired) throw new RuntimeException('AI is disabled; paired production finalization is fail-closed.');
                $stage = 'PAIRED_PERSIST';
                $result = $paired->run($experimentId, $runId, (int) $asset['id'], $horizon, $cutoff->value(), $snapshot['id'], $snapshot['feature_snapshot_hash'], $featureSet, (float) $last['close'], $cutoff->value());
                $stage = 'QUANT_TARGET_CREATE';
                $targets->create((int) $result['quant_final_id'], $asset['asset_type'], $cutoff->at, $horizon);
                $stage = 'AI_TARGET_CREATE';
                $targets->create((int) $result['ai_final_id'], $asset['asset_type'], $cutoff->at, $horizon);
                $created += 2;
            } catch (Throwable $error) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $event = $pdo->prepare("INSERT INTO system_events(severity,event_type,message,metadata_json,created_at) VALUES('ERROR','PAIRED_CYCLE_FAILED',?,?,?)");
                $event->execute(['No paired FINAL predictions were committed.', json_encode(['run_id' => $runId, 'asset_id' => $asset['id'], 'horizon' => $horizon, 'stage' => $stage, 'error_class' => $error::class, 'error_code' => $error->getCode(), 'error_message' => substr($error->getMessage(), 0, 300)]), gmdate('c')]);
                throw $error;
            }
        }
    }
    $pdo->prepare("UPDATE forecast_runs SET status='FINALIZED',state_detail='FINALIZED',completed_at=? WHERE id=?")->execute([gmdate('c'), $runId]);
    $pdo->commit();
    echo json_encode(['run_key' => $runKey, 'information_cutoff' => $cutoff->value(), 'final_predictions' => $created, 'paired' => $aiEnabled], JSON_THROW_ON_ERROR) . PHP_EOL;
} catch (Throwable $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $pdo->prepare("UPDATE forecast_runs SET status='FAILED',state_detail=?,completed_at=? WHERE id=?")->execute([substr($error->getMessage(), 0, 500), gmdate('c'), $runId]);
    throw $error;
}
