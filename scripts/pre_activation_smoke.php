<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use MarketForecast\AI\AiProductionGate;
use MarketForecast\Database\Connection;

function envPresent(string $name, string $file): bool
{
    if (getenv($name) !== false && getenv($name) !== '') return true;
    foreach (@file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        if (!str_contains($line, '=')) continue;
        [$key, $value] = explode('=', trim($line), 2);
        if (trim($key) === $name) return trim($value, " '\"") !== '';
    }
    return false;
}

$database = $argv[1] ?? dirname(__DIR__) . '/database/forecast.sqlite';
$environment = $argv[2] ?? dirname(__DIR__) . '/.env';
$pdo = Connection::open($database);
$aiFlag = envPresent('AI_ENABLED', $environment) ? 'true' : 'false';
$apiPresent = envPresent('OPENAI_API_KEY', $environment);
$readiness = AiProductionGate::runtimeReadiness($pdo, $aiFlag, $apiPresent ? 'present' : '');
$cronFiles = ['run_forecast.php','capture_reference_prices.php','resolve_outcomes.php','run_frf_windows.php','run_benchmarks.php'];
$missingCron = array_values(array_filter($cronFiles, static fn(string $file): bool => !is_file(dirname(__DIR__) . '/cron/' . $file)));
$safe = $readiness['schema_ready'] && $readiness['cutoff_ready'] && $readiness['snapshot_ready'] && $readiness['pairing_ready'] && $readiness['target_lifecycle_ready'] && $readiness['evaluation_ready'] && $readiness['benchmark_ready'] && $readiness['rolling_frf_ready'] && $readiness['reference_rule_ready'] && $missingCron === [];
echo json_encode(['status' => $safe ? 'PASS' : 'FAIL', 'ai_network_called' => false, 'trading_enabled' => false, 'paper_trading_enabled' => false, 'missing_cron_files' => $missingCron, 'readiness' => $readiness], JSON_THROW_ON_ERROR) . PHP_EOL;
exit($safe ? 0 : 1);
