<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\AI\AiProductionGate;
use MarketForecast\Database\{Connection,Migrator};
use PHPUnit\Framework\TestCase;
final class AiRuntimeGateTest extends TestCase
{
 public function testRuntimeGateReportsDisabledEvenWhenSchemaIsReady():void{$p=tempnam(sys_get_temp_dir(),'gate-');$db=Connection::open($p);(new Migrator($db))->apply(dirname(__DIR__).'/database/migrations','now');$r=AiProductionGate::runtimeReadiness($db,'false','key');self::assertFalse($r['enabled']);self::assertTrue($r['schema_ready']);self::assertTrue($r['pairing_ready']);self::assertSame([], $r['missing_tables']);}
 public function testRuntimeGateRejectsWrongReferenceRule():void{$p=tempnam(sys_get_temp_dir(),'gate-');$db=Connection::open($p);(new Migrator($db))->apply(dirname(__DIR__).'/database/migrations','now');$this->expectException(\RuntimeException::class);AiProductionGate::assertRuntimeReady($db,'true','key','REFERENCE_PRICE_RULE_V1');}
}
