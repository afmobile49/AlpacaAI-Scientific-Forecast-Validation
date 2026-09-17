<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\Database\{Connection,BackupService};use PHPUnit\Framework\TestCase;
final class BackupHealthTest extends TestCase { public function testBackupCreatesReadableSqlite():void{$d=tempnam(sys_get_temp_dir(),'db-');$b=tempnam(sys_get_temp_dir(),'bak-');@unlink($b);$pdo=Connection::open($d);$pdo->exec('CREATE TABLE probe (id INTEGER); INSERT INTO probe VALUES (1)');(new BackupService($pdo))->create($b);$copy=Connection::open($b);self::assertSame(1,(int)$copy->query('SELECT COUNT(*) FROM probe')->fetchColumn());@unlink($d);@unlink($b);@unlink($d.'-wal');@unlink($d.'-shm');} }
