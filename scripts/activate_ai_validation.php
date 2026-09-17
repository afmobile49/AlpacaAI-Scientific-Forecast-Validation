<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use MarketForecast\AI\AiProductionGate;
use MarketForecast\Database\Connection;

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
$pdo = Connection::open($database);
$flag = envValue('AI_ENABLED', $environment);
$key = envValue('OPENAI_API_KEY', $environment);
AiProductionGate::assertRuntimeReady($pdo, $flag, $key);

$pdo->beginTransaction();
try {
    $experiment = $pdo->prepare("SELECT id,status,ai_activation_at FROM experiments WHERE experiment_code='EXP-001'");
    $experiment->execute();
    $row = $experiment->fetch(PDO::FETCH_ASSOC);
    if (!$row) throw new RuntimeException('EXP-001 does not exist.');
    if ($row['ai_activation_at'] !== null) {
        $pdo->commit();
        echo json_encode(['status' => 'ALREADY_ACTIVATED', 'activated_at' => $row['ai_activation_at']], JSON_THROW_ON_ERROR) . PHP_EOL;
        exit(0);
    }
    $now = gmdate('c');
    $update = $pdo->prepare("UPDATE experiments SET ai_activation_at=?,status='ACTIVE_VALIDATION' WHERE id=? AND ai_activation_at IS NULL");
    $update->execute([$now, $row['id']]);
    if ($update->rowCount() !== 1) throw new RuntimeException('Atomic activation transition failed.');
    $event = $pdo->prepare("INSERT INTO system_events(severity,event_type,message,metadata_json,created_at) VALUES('INFO','AI_QUANT_VALIDATION_ACTIVATED',?,?,?)");
    $event->execute(['Prospective paired validation activated.', json_encode(['experiment_id' => (int) $row['id'], 'activation_at' => $now], JSON_THROW_ON_ERROR), $now]);
    $pdo->commit();
    echo json_encode(['status' => 'ACTIVATED', 'activated_at' => $now], JSON_THROW_ON_ERROR) . PHP_EOL;
} catch (Throwable $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    throw $error;
}
