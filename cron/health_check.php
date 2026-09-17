<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/Database/Connection.php';
use MarketForecast\Database\Connection;
$path=$argv[1]??throw new InvalidArgumentException('Database path required.');
$pdo=Connection::open($path);$ok=(int)$pdo->query('SELECT 1')->fetchColumn()===1;echo json_encode(['status'=>$ok?'ok':'failed','database_readable'=>$ok,'trading_enabled'=>false,'paper_trading_enabled'=>false,'checked_at'=>gmdate('c')],JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES),PHP_EOL;exit($ok?0:50);
