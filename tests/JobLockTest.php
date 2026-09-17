<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\Database\Connection;use MarketForecast\Database\Migrator;use MarketForecast\Jobs\JobLock;use PHPUnit\Framework\TestCase;
final class JobLockTest extends TestCase { public function testDuplicateJobIsSkipped():void{$p=tempnam(sys_get_temp_dir(),'job-');$pdo=Connection::open($p);(new Migrator($pdo))->apply(dirname(__DIR__).'/database/migrations','2026-01-01T00:00:00Z');$l=new JobLock($pdo);self::assertTrue($l->acquire('x','test','2026-01-01T00:00:00Z'));self::assertFalse($l->acquire('x','test','2026-01-01T00:00:00Z'));$l->finish('x','SUCCESS','2026-01-01T01:00:00Z');self::assertSame('SUCCESS',$pdo->query("SELECT status FROM system_jobs WHERE job_key='x'")->fetchColumn());@unlink($p);@unlink($p.'-wal');@unlink($p.'-shm');} }
