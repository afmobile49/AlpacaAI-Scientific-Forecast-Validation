<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\Database\{Connection,Migrator};use MarketForecast\Jobs\{JobLock,JobRunner};use PHPUnit\Framework\TestCase;
final class JobRunnerTest extends TestCase
{
 public function testSuccessAndDuplicateAreObservable():void{$p=tempnam(sys_get_temp_dir(),'jr-');$db=Connection::open($p);(new Migrator($db))->apply(dirname(__DIR__).'/database/migrations','2026-01-01');$r=new JobRunner(new JobLock($db));self::assertSame('SUCCESS',$r->run('k','demo',fn()=>['x'=>1],'2026-01-01')['status']);self::assertSame('SKIPPED',$r->run('k','demo',fn()=>['x'=>2],'2026-01-01')['status']);self::assertSame('SUCCESS',$db->query("SELECT status FROM system_jobs WHERE job_key='k'")->fetchColumn());@unlink($p);}
}
