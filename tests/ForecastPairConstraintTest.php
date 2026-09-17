<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\AI\ForecastPairRepository;
use MarketForecast\Database\{Connection,Migrator};
use PHPUnit\Framework\TestCase;
final class ForecastPairConstraintTest extends TestCase
{
 public function testPairIsIdempotentAndRejectsIdentityConflict():void{$p=tempnam(sys_get_temp_dir(),'pair-');$db=Connection::open($p);(new Migrator($db))->apply(dirname(__DIR__).'/database/migrations','now');$db->exec("INSERT INTO experiments(experiment_code,name,description,start_at,status,config_json) VALUES('E','E','E','now','ACTIVE','{}');INSERT INTO assets(symbol,display_name,asset_type,provider,provider_symbol,timezone) VALUES('T','T','STOCK','TEST','T','UTC');INSERT INTO market_snapshots(experiment_id,asset_id,information_cutoff,snapshot_time,features_json,source_data_hash,feature_set_version,feature_snapshot_hash,market_data_fingerprint,market_bar_count) VALUES(1,1,'2026-01-01','2026-01-01','{}','m','FEATURE_SET_V1','f','m',1);");$r=new ForecastPairRepository($db);$x=['experiment_id'=>1,'asset_id'=>1,'horizon_code'=>'1D','forecast_cycle_id'=>'c','information_cutoff'=>'2026-01-01','market_snapshot_id'=>1,'feature_snapshot_hash'=>'f','created_at'=>'now'];$id=$r->create($x);self::assertSame($id,$r->create($x));$x['information_cutoff']='2026-01-02';$this->expectException(\RuntimeException::class);$r->create($x);}
}
