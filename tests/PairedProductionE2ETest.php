<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\AI\{ForecastPairRepository,OpenAIProvider,PairedForecastOrchestrator,PairedStatistics};
use MarketForecast\Database\{Connection,Migrator};
use MarketForecast\Evaluation\{BenchmarkEngine,ForecastTargetRepository,MetricsCalculator,OutcomeResolver};
use MarketForecast\Forecast\{ForecastFreezer,QuantForecastEngine};
use MarketForecast\Reliability\FrfWindowAggregator;
use MarketForecast\Reports\{DailyHtmlRenderer,DailyReportDataBuilder,DailyReportViewModel};
use PHPUnit\Framework\TestCase;

final class PairedProductionE2ETest extends TestCase
{
    public function testOfflinePairedLifecycleProducesOfficialStatisticsBenchmarkFrfAndHtml(): void
    {
        $path=tempnam(sys_get_temp_dir(),'paired-production-');$db=Connection::open($path);(new Migrator($db))->apply(dirname(__DIR__).'/database/migrations','now');
        $db->exec("INSERT INTO experiments(experiment_code,name,description,start_at,status,config_json,ai_activation_at) VALUES('EXP-001','E','E','now','ACTIVE_VALIDATION','{}','2026-01-01T00:00:00Z');INSERT INTO forecast_runs(experiment_id,run_key,scheduled_for,status,state_detail,information_cutoff) VALUES(1,'fixture','2026-09-11','RUNNING','CREATED','2026-09-11T00:00:00Z');INSERT INTO assets(symbol,display_name,asset_type,provider,provider_symbol,timezone) VALUES('SPY','SPY','STOCK','TEST','SPY','America/New_York');INSERT INTO market_snapshots(experiment_id,asset_id,information_cutoff,snapshot_time,features_json,source_data_hash,feature_set_version,feature_snapshot_hash,market_data_fingerprint,market_bar_count) VALUES(1,1,'2026-09-11T00:00:00Z','2026-09-11T00:00:00Z','{}','market','FEATURE_SET_V1','feature','market',60)");
        $ai=new OpenAIProvider('test','test','https://invalid',static fn()=>['output_text'=>'{"direction":"BULLISH","probability_up":0.7,"probability_neutral":0.2,"probability_down":0.1,"expected_return_pct":1.2,"confidence":0.7,"reason_codes":["POSITIVE_TREND"],"explanation":"offline"}']);
        $features=['close'=>110.0,'sma20'=>108.0,'sma50'=>105.0,'ema20'=>109.0,'ema50'=>106.0,'rsi14'=>60.0,'atr14'=>2.0,'momentum5'=>2.0,'momentum20'=>4.0];
        $orchestrator=new PairedForecastOrchestrator($db,new QuantForecastEngine(),$ai,new ForecastPairRepository($db),new ForecastFreezer());
        $pair=$orchestrator->run(1,1,1,'1D','2026-09-11T00:00:00Z',1,'feature',$features,100.0,'2026-09-11T00:00:00Z');
        $targets=new ForecastTargetRepository($db);foreach([$pair['quant_final_id'],$pair['ai_final_id']] as $id){$target=$targets->create($id,'STOCK',new \DateTimeImmutable('2026-09-11T00:00:00Z'),'1D');$db->prepare("UPDATE forecast_targets SET status='RESOLVED',reference_status='REFERENCE_CAPTURED',reference_price=100,reference_price_time='2026-09-11T13:30:00Z' WHERE id=?")->execute([$target]);$actual=(new OutcomeResolver())->returnPct(100,102);$direction=(new OutcomeResolver())->direction($actual);$db->prepare("INSERT INTO actual_outcomes(final_prediction_id,actual_price,actual_price_time,actual_return_pct,actual_direction,source_provider,source_hash) VALUES(?,?,?,?,?,?,?)")->execute([$id,102,'2026-09-12T20:00:00Z',$actual,$direction,'FIXTURE','outcome-'.$id]);$final=$db->query('SELECT direction,probabilities_json FROM final_predictions WHERE id='.(int)$id)->fetch(\PDO::FETCH_ASSOC);$p=json_decode($final['probabilities_json'],true);$m=new MetricsCalculator();$db->prepare("INSERT INTO evaluation_results(final_prediction_id,direction_correct,absolute_error_pct,squared_error,brier_score,calibration_bucket,evaluated_at) VALUES(?,?,?,?,?,?,?)")->execute([$id,$m->directionCorrect($final['direction'],$direction)?1:0,0,0,$m->brier($p[0],$p[1],$p[2],$direction),'HIGH','2026-09-12T20:00:00Z']);}
        $stats=(new PairedStatistics($db))->summary(1);self::assertSame(1,$stats['paired_eligible_count']);self::assertSame(1,$stats['paired_resolved_count']);
        $quant=$db->query("SELECT qp.features_json,fp.direction FROM final_predictions fp JOIN quant_predictions qp ON qp.id=fp.source_prediction_id WHERE fp.id=".(int)$pair['quant_final_id'])->fetch(\PDO::FETCH_ASSOC);$benchmark=(new BenchmarkEngine())->predict('MOMENTUM_5',json_decode($quant['features_json'],true)+['quant_direction'=>$quant['direction']]);self::assertContains($benchmark,['BULLISH','NEUTRAL','BEARISH']);
        $frf=(new FrfWindowAggregator())->calculate([['actual_price_time'=>'2026-09-12T20:00:00Z','evaluated_at'=>'2026-09-12T20:00:00Z','direction_correct'=>1,'brier_score'=>.14,'absolute_error_pct'=>0]],new \DateTimeImmutable('2026-09-13T00:00:00Z'));self::assertSame(1,$frf['CUMULATIVE']['resolved_n']);
        $builder=new DailyReportDataBuilder($db);$data=$builder->build(gmdate('Y-m-d'),'fixture');$html=(new DailyHtmlRenderer())->render((new DailyReportViewModel())->build($data));self::assertStringContainsString('SPY',$html);self::assertStringContainsString('زوج‌های واجد شرایط',$html);self::assertCount(2,$data['forecasts']);$otherCycle=$builder->build(gmdate('Y-m-d'),'other-cycle');self::assertSame(0,$otherCycle['summary']['forecast_count']);@unlink($path);
    }
}
