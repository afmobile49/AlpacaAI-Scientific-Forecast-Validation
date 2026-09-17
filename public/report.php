<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=UTF-8');
$date = $_GET['date'] ?? '';
$cycle = $_GET['cycle'] ?? 'daily';
if (!is_string($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !is_string($cycle) || !in_array($cycle, ['daily','stock','crypto'], true)) {
    http_response_code(400);
    exit('درخواست نامعتبر است.');
}
$suffix = $cycle === 'daily' ? '' : '-'.$cycle;
$file = '/home/rayanhos/private/AlpacaAIApp/storage/reports/report-'.$date.$suffix.'.html';
if (!is_readable($file)) {
    http_response_code(404);
    exit('گزارش پیدا نشد.');
}
readfile($file);
