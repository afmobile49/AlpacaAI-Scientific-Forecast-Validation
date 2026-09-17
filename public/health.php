<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');
echo json_encode(['status'=>'ok','trading_enabled'=>false,'paper_trading_enabled'=>false,'checked_at'=>gmdate('c')],JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES),PHP_EOL;
