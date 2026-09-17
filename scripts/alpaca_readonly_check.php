<?php
declare(strict_types=1);

$path = $argv[1] ?? throw new InvalidArgumentException('Env path required.');
$values = [];
foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
    if (trim($line) === '' || str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
    [$key, $value] = explode('=', $line, 2);
    $values[trim($key)] = trim($value, " \t\\\"'");
}
foreach (['ALPACA_API_KEY','ALPACA_API_SECRET','ALPACA_DATA_BASE_URL'] as $key) if (($values[$key] ?? '') === '') throw new RuntimeException($key . ' missing.');
$url = rtrim($values['ALPACA_DATA_BASE_URL'], '/') . '/v2/stocks/SPY/bars/latest?feed=iex';
$ch = curl_init($url);
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_HTTPHEADER => ['APCA-API-KEY-ID: ' . $values['ALPACA_API_KEY'], 'APCA-API-SECRET-KEY: ' . $values['ALPACA_API_SECRET'], 'Accept: application/json']]);
$body = curl_exec($ch); $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE); $error = curl_error($ch); curl_close($ch);
if ($body === false || $error !== '') throw new RuntimeException('HTTP client error.');
echo 'key_fields=present data_url=' . $values['ALPACA_DATA_BASE_URL'] . ' http_status=' . $status . PHP_EOL;
if ($status >= 200 && $status < 300) { $json = json_decode((string)$body, true); echo 'response_shape=' . (is_array($json) && isset($json['bar']) ? 'latest_bar' : 'unexpected') . PHP_EOL; }
