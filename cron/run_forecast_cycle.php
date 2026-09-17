<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use MarketForecast\Jobs\CyclePipeline;

if ($argc < 5) {
    throw new InvalidArgumentException('Usage: run_forecast_cycle.php DB ENV ARCHIVE_DIR stock|crypto');
}

[$database, $environment, $archive, $cycle] = array_slice($argv, 1, 4);
$date = (new DateTimeImmutable('now', new DateTimeZone('America/New_York')))->format('Y-m-d');

$run = static function (string $script, array $arguments): array {
    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/' . $script);
    foreach ($arguments as $argument) {
        $command .= ' ' . escapeshellarg($argument);
    }
    exec($command . ' 2>&1', $output, $exitCode);
    if ($exitCode !== 0) {
        throw new RuntimeException($script . ' failed: ' . implode("\n", $output));
    }
    $json = json_decode((string) end($output), true);
    if (!is_array($json)) {
        throw new RuntimeException($script . ' did not return JSON.');
    }
    return $json;
};

$result = (new CyclePipeline())->run(
    static fn(): array => $run('run_forecast.php', [$database, $environment, $cycle]),
    static fn(): array => $run('generate_daily_report.php', [$date, $archive, $database, $cycle]),
    static fn(): array => $run('send_daily_report.php', [$date, $database, $cycle, $environment]),
);

echo json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
