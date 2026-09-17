<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\Database\{Connection,Migrator};use PHPUnit\Framework\TestCase;
final class FrfMigrationTest extends TestCase{public function testMigrationsAreIdempotent():void{$p=tempnam(sys_get_temp_dir(),'frf-');$db=Connection::open($p);$m=new Migrator($db);self::assertSame(13,$m->apply(dirname(__DIR__).'/database/migrations','2026-01-01T00:00:00Z'));self::assertSame(0,$m->apply(dirname(__DIR__).'/database/migrations','2026-01-01T00:00:00Z'));foreach(['reliability_runs','reliability_scores','reliability_group_scores','forecast_regimes','daily_aggregates','quant_scores'] as $t)self::assertSame(1,(int)$db->query("select count(*) from sqlite_master where type='table' and name='$t'")->fetchColumn());@unlink($p);@unlink($p.'-wal');@unlink($p.'-shm');}}



