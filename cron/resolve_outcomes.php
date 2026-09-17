<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use MarketForecast\Database\Connection;
use MarketForecast\Evaluation\{OutcomeResolver, TargetTimePriceResolver};
use MarketForecast\MarketData\AlpacaProvider;
use MarketForecast\Support\CanonicalHasher;

function valueOf(string $name, string $file): string
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
$key = valueOf('ALPACA_API_KEY', $environment);
$secret = valueOf('ALPACA_API_SECRET', $environment);
$base = valueOf('ALPACA_DATA_BASE_URL', $environment) ?: 'https://data.alpaca.markets';
if ($key === '' || $secret === '') throw new RuntimeException('Alpaca credentials are not configured.');

$pdo = Connection::open($database);
$rows = $pdo->query("SELECT ft.id target_id,ft.final_prediction_id,ft.target_time,ft.reference_price,ft.reference_status,a.asset_type,a.provider_symbol FROM forecast_targets ft JOIN final_predictions fp ON fp.id=ft.final_prediction_id JOIN assets a ON a.id=fp.asset_id WHERE ft.status='PENDING' AND ft.reference_status='REFERENCE_CAPTURED' AND julianday(ft.target_time)<=julianday('now') AND NOT EXISTS(SELECT 1 FROM actual_outcomes ao WHERE ao.final_prediction_id=ft.final_prediction_id)")->fetchAll(PDO::FETCH_ASSOC);
$provider = new AlpacaProvider($base, $key, $secret);
$resolver = new OutcomeResolver();
$targetResolver = new TargetTimePriceResolver($provider);
$resolved = 0;
foreach ($rows as $row) {
    try {
        $bar = $targetResolver->resolve($row['provider_symbol'], $row['asset_type'], $row['target_time']);
        $actual = (float) $bar['close'];
        $return = $resolver->returnPct((float) $row['reference_price'], $actual);
        $direction = $resolver->direction($return);
        $hash = CanonicalHasher::hash([$row['final_prediction_id'], $actual, $bar['time'], $return, $direction, $bar['provider']]);
        $pdo->beginTransaction();
        $pdo->prepare('INSERT OR IGNORE INTO actual_outcomes(final_prediction_id,actual_price,actual_price_time,actual_return_pct,actual_direction,source_provider,source_hash) VALUES(?,?,?,?,?,?,?)')->execute([$row['final_prediction_id'], $actual, $bar['time'], $return, $direction, $bar['provider'], $hash]);
        $pdo->prepare("UPDATE forecast_targets SET status='RESOLVED',resolved_at=? WHERE id=? AND status='PENDING'")->execute([gmdate('c'), $row['target_id']]);
        $pdo->commit();
        $resolved++;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        fwrite(STDERR, 'outcome deferred: ' . $error->getMessage() . PHP_EOL);
    }
}
echo json_encode(['resolved' => $resolved], JSON_THROW_ON_ERROR) . PHP_EOL;
