<?php
declare(strict_types=1);
namespace MarketForecast\Database;
use PDO;
final class BackupService { public function __construct(private readonly PDO $pdo) {} public function create(string $path):void{$dir=dirname($path);if(!is_dir($dir)&&!mkdir($dir,0775,true)&&!is_dir($dir))throw new \RuntimeException('Backup directory unavailable.');$safe=str_replace("'","''",$path);$this->pdo->exec("VACUUM INTO '$safe'");} }
