<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\AI\{AiPredictionService,OpenAIProvider};
use MarketForecast\Database\{Connection,Migrator};
use PHPUnit\Framework\TestCase;
final class AiPredictionServiceTest extends TestCase
{
 public function testValidAiPredictionPersists():void{$p=tempnam(sys_get_temp_dir(),'ai-');$db=Connection::open($p);(new Migrator($db))->apply(dirname(__DIR__).'/database/migrations','now');$db->exec("INSERT INTO experiments(experiment_code,name,description,start_at,status,config_json) VALUES('E','E','E','now','ACTIVE','{}');INSERT INTO forecast_runs(experiment_id,run_key,scheduled_for,status,state_detail) VALUES(1,'r','now','RUNNING','CREATED');INSERT INTO assets(symbol,display_name,asset_type,provider,provider_symbol,timezone) VALUES('T','T','STOCK','TEST','T','UTC');");$provider=new OpenAIProvider('test','test','https://invalid',fn()=>['output_text'=>'{"direction":"BULLISH","probability_up":0.7,"probability_neutral":0.2,"probability_down":0.1,"expected_return_pct":1.2,"confidence":0.7,"reason_codes":["POSITIVE_TREND"],"explanation":"fixture"}']);self::assertNotNull((new AiPredictionService($db,$provider))->predict(1,1,'1D',['close'=>100],'2026-09-11T00:00:00Z'));self::assertSame(1,(int)$db->query('SELECT COUNT(*) FROM ai_predictions')->fetchColumn());}
}
