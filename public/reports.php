<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=UTF-8');
$e = static fn(mixed $value): string => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$directory = '/home/rayanhos/private/AlpacaAIApp/storage/reports';
$files = is_dir($directory) ? (glob($directory.'/report-*.html') ?: []) : [];
usort($files, static fn(string $left, string $right): int => strcmp(basename($right), basename($left)));
$aggregate = ['aggregate_date'=>'—','forecast_count'=>null,'resolved_count'=>null,'accuracy'=>null];
$database = '/home/rayanhos/private/AlpacaAIApp/database/forecast.sqlite';
if (is_readable($database)) {
    try {
        $pdo = new PDO('sqlite:'.$database);
        $aggregate = $pdo->query('SELECT aggregate_date,forecast_count,resolved_count,accuracy FROM daily_aggregates ORDER BY aggregate_date DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC) ?: $aggregate;
    } catch (Throwable) {}
}
$cycleLabel = static fn(string $cycle): string => match ($cycle) {'crypto'=>'کریپتو', 'stock'=>'سهام', default=>'روزانه'};
?><!doctype html><html lang="fa" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>پنل گزارش‌های AlpacaAIApp</title><style>body{margin:0;background:#f1f4f8;color:#182234;font-family:Tahoma,Arial;direction:rtl}.wrap{max-width:900px;margin:auto;padding:20px}.head,.card{background:#fff;border:1px solid #e2e7ed;border-radius:12px;padding:18px;margin:12px 0}.head{background:#111827;color:#fff}.head h1{margin:0 0 8px;font-size:22px}.metrics{display:flex;gap:10px}.m{flex:1;border:1px solid #e2e7ed;border-radius:9px;padding:14px;text-align:center}.big{display:block;font-size:22px;font-weight:bold;margin-top:8px}a{color:#1769e0;text-decoration:none}li{padding:8px}</style></head><body><div class="wrap"><div class="head"><h1>پنل گزارش‌های AlpacaAIApp</h1><div>داشبورد خواندنی ارزیابی پیش‌بینی بازار · آخرین روز: <?=$e($aggregate['aggregate_date'])?></div></div><div class="card"><div class="metrics"><div class="m">پیش‌بینی‌ها<span class="big"><?=$e($aggregate['forecast_count']??'—')?></span></div><div class="m">نتایج تکمیل‌شده<span class="big"><?=$e($aggregate['resolved_count']??'—')?></span></div><div class="m">دقت<span class="big"><?=$e($aggregate['accuracy']===null?'—':round((float)$aggregate['accuracy']*100,2).'%')?></span></div></div></div><div class="card"><h2>گزارش‌های آرشیوشده</h2><?php if(!$files): ?><p>گزارشی موجود نیست.</p><?php else: ?><ul><?php foreach($files as $file): $name=basename($file); if(!preg_match('/^report-(\d{4}-\d{2}-\d{2})(?:-(stock|crypto))?\.html$/',$name,$match)) continue; $day=$match[1]; $cycle=$match[2]??'daily'; ?><li><a href="report.php?date=<?=$e($day)?>&amp;cycle=<?=$e($cycle)?>">مشاهدهٔ گزارش <?=$e($cycleLabel($cycle))?> روز <span dir="ltr"><?=$e($day)?></span></a></li><?php endforeach; ?></ul><?php endif; ?></div></div></body></html>
