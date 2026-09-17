<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/Database/Connection.php'; require dirname(__DIR__).'/app/Database/BackupService.php';
use MarketForecast\Database\{Connection,BackupService};
$db=$argv[1]??throw new InvalidArgumentException('Database path required.');$out=$argv[2]??throw new InvalidArgumentException('Backup path required.');
(new BackupService(Connection::open($db)))->create($out);echo "backup_created=1 path=$out\n";
