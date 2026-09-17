<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\Database\{Connection,Migrator};use MarketForecast\Jobs\{JobLock,JobRunner};use PHPUnit\Framework\TestCase;
final class RunWithJobTest extends TestCase
{
 public function testJobRunnerPersistsTerminalState():void{$p=tempnam(sys_get_temp_dir(),'wrap-');$db=Connection::open($p);(new Migrator($db))->apply(dirname(__DIR__).'/database/migrations','2026-01-01');$r=(new JobRunner(new JobLock($db)))->run('daily:demo','Demo','2026-01-01'=== '2026-01-01'?fn()=>['ok'=>true]:fn()=>null,'2026-01-01');self::assertSame('SUCCESS',$r['status']);self::assertSame('SUCCESS',$db->query("SELECT status FROM system_jobs WHERE job_key='daily:demo'")->fetchColumn());@unlink($p);}
}
