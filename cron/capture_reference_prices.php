<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use MarketForecast\Database\Connection;
use MarketForecast\Evaluation\ReferencePriceRuleV2;
use MarketForecast\MarketData\{AlpacaProvider, MarketCalendarService};

function envV(string $name, string $file): string
{
    $value = getenv($name);
    if ($value !== false && $value !== '') return $value;
    foreach (@file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        if (!str_starts_with(trim($line), $name . '=')) continue;
        return trim(explode('=', trim($line), 2)[1], " '\"");
    }
    return '';
}

function stockSessionBar(array $bars, DateTimeImmutable $cutoff, MarketCalendarService $calendar): ?array
{
    foreach ($calendar->nextSessions($cutoff, 3) as $session) {
        $open = $calendar->sessionOpen($session);
        if ($open === null || $open < $cutoff) continue;
        $date = $session->setTimezone(new DateTimeZone('America/New_York'))->format('Y-m-d');
        foreach ($bars as $bar) {
            if ((new DateTimeImmutable($bar['time']))->setTimezone(new DateTimeZone('America/New_York'))->format('Y-m-d') === $date) return $bar;
        }
        return null;
    }
    return null;
}

$database = $argv[1] ?? dirname(__DIR__) . '/database/forecast.sqlite';
$environment = $argv[2] ?? dirname(__DIR__) . '/.env';
$key = envV('ALPACA_API_KEY', $environment);
$secret = envV('ALPACA_API_SECRET', $environment);
$base = envV('ALPACA_DATA_BASE_URL', $environment) ?: 'https://data.alpaca.markets';
if ($key === '' || $secret === '') throw new RuntimeException('Alpaca credentials are not configured.');

$pdo = Connection::open($database);
$api = new AlpacaProvider($base, $key, $secret);
$calendar = new MarketCalendarService();
$statement = $pdo->query("SELECT ft.id,fp.information_cutoff,a.provider_symbol,a.asset_type FROM forecast_targets ft JOIN final_predictions fp ON fp.id=ft.final_prediction_id JOIN assets a ON a.id=fp.asset_id WHERE ft.reference_status='REFERENCE_PENDING' AND ft.reference_rule IN ('REFERENCE_PRICE_RULE_V2','CRYPTO_REFERENCE_PRICE_RULE_V1')");
$captured = 0;
foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
    try {
        $cutoff = new DateTimeImmutable($row['information_cutoff']);
        if ($row['asset_type'] === 'STOCK') {
            $bars = $api->getHistoricalBars($row['provider_symbol'], '1Day', $cutoff->modify('-1 day'), $cutoff->modify('+5 days'));
            $bar = stockSessionBar($bars, $cutoff, $calendar);
            if ($bar === null) continue; // Market session has not produced a usable bar yet; retry on the next cron run.
            $price = ReferencePriceRuleV2::stockReference($bar);
            $rule = ReferencePriceRuleV2::STOCK;
        } else {
            $bars = $api->getHistoricalBars($row['provider_symbol'], '1Min', $cutoff, $cutoff->modify('+60 minutes'));
            $price = ReferencePriceRuleV2::cryptoReference($bars, $cutoff, 60);
            $bar = $bars[0] ?? null;
            if ($bar === null) continue;
            $rule = ReferencePriceRuleV2::CRYPTO;
        }
        $update = $pdo->prepare("UPDATE forecast_targets SET reference_price=?,reference_price_time=?,reference_status='REFERENCE_CAPTURED',reference_rule=? WHERE id=? AND reference_status='REFERENCE_PENDING'");
        $update->execute([$price, $bar['time'], $rule, $row['id']]);
        $captured += $update->rowCount();
    } catch (Throwable $error) {
        fwrite(STDERR, 'reference deferred: ' . $error->getMessage() . PHP_EOL);
    }
}
echo json_encode(['references_captured' => $captured], JSON_THROW_ON_ERROR) . PHP_EOL;
