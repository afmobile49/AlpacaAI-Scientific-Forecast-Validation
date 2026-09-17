<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/Database/Connection.php';
require dirname(__DIR__).'/app/AI/AiProductionGate.php';
$path=dirname(__DIR__).'/database/forecast.sqlite';$pdo=MarketForecast\Database\Connection::open($path);$env=dirname(__DIR__).'/.env';$flag='';$key='';foreach(@file($env,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES)?:[] as $l){if(str_starts_with($l,'AI_ENABLED='))$flag=trim(explode('=',$l,2)[1]);if(str_starts_with($l,'OPENAI_API_KEY='))$key=trim(explode('=',$l,2)[1]);} $r=MarketForecast\AI\AiProductionGate::runtimeReadiness($pdo,$flag,$key);$r['api_key_present']=$key!=='';unset($r['enabled']);echo json_encode($r,JSON_THROW_ON_ERROR).PHP_EOL;
