<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\AI\PairedStatistics;
use MarketForecast\Database\{Connection,Migrator};
use PHPUnit\Framework\TestCase;
final class PairedStatisticsTest extends TestCase
{
 public function testPreAiAndUnpairedRowsAreExcluded():void{$p=tempnam(sys_get_temp_dir(),'stats-');$db=Connection::open($p);(new Migrator($db))->apply(dirname(__DIR__).'/database/migrations','now');$db->exec("INSERT INTO experiments(experiment_code,name,description,start_at,status,config_json,ai_activation_at) VALUES('E','E','E','now','ACTIVE','{}','2026-09-11T00:00:00Z');");self::assertSame(0,(new PairedStatistics($db))->eligibleCount(1));}
}
