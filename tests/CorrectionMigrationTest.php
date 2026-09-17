<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\Database\{Connection,Migrator};
use PHPUnit\Framework\TestCase;
final class CorrectionMigrationTest extends TestCase
{
 public function testNullableReturnMigrationPreservesRowsAndForeignKeys():void
 {
  $p=tempnam(sys_get_temp_dir(),'mig-');$db=Connection::open($p);(new Migrator($db))->apply(dirname(__DIR__).'/database/migrations','2026-01-01T00:00:00Z');
  $db->exec("INSERT INTO experiments(experiment_code,name,description,start_at,status,config_json) VALUES('E','E','E','2026','ACTIVE','{}');INSERT INTO forecast_runs(experiment_id,run_key,scheduled_for,status,state_detail) VALUES(1,'r','2026','FINALIZED','OK');INSERT INTO assets(symbol,display_name,asset_type,provider,provider_symbol,timezone) VALUES('T','T','STOCK','TEST','T','UTC');INSERT INTO quant_predictions(run_id,asset_id,horizon_code,direction,probabilities_json,expected_return_pct,confidence,features_json,algorithm_version) VALUES(1,1,'1D','BULLISH','[1,0,0]',0,0.7,'{}','QUANT_V1');");
  $old=(int)$db->query('SELECT id FROM quant_predictions')->fetchColumn();(new Migrator($db))->apply(dirname(__DIR__).'/database/migrations','2026-01-02T00:00:00Z');
  self::assertSame($old,(int)$db->query('SELECT id FROM quant_predictions')->fetchColumn());$columns=$db->query("PRAGMA table_info('quant_predictions')")->fetchAll(\PDO::FETCH_ASSOC);$col=array_values(array_filter($columns,static fn($c)=>$c['name']==='expected_return_pct'));self::assertSame(0,(int)$col[0]['notnull']);self::assertSame([], $db->query('PRAGMA foreign_key_check')->fetchAll());@unlink($p);
 }
}
