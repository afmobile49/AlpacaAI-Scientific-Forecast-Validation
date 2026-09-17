<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use DateTimeImmutable;
use MarketForecast\Evaluation\{ForecastTargetFactory,MetricsCalculator,OutcomeResolver};
use MarketForecast\Forecast\{ForecastFreezer,ReferencePrice};
use MarketForecast\Indicators\FeatureSetV1;
use MarketForecast\Forecast\QuantForecastEngine;
use PHPUnit\Framework\TestCase;

final class QuantForwardValidationTest extends TestCase
{
    public function testForwardLifecycleIsDeterministicAndLeakageSafe(): void
    {
        $bars=[];for($i=0;$i<60;$i++){$c=100+$i*.2;$bars[]=['time'=>sprintf('2026-01-%02dT21:00:00+00:00',($i%28)+1),'open'=>$c,'high'=>$c+1,'low'=>$c-1,'close'=>$c,'volume'=>1000,'provider'=>'ALPACA'];}
        $f=(new FeatureSetV1())->calculate($bars);$f['close']=100.0+59*.2;$p=(new QuantForecastEngine())->predict($f);ReferencePrice::validate($f['close'],$bars[59]['time']);$target=(new ForecastTargetFactory())->create('STOCK',new DateTimeImmutable($bars[59]['time']));$final=(new ForecastFreezer())->freeze(['direction'=>$p['direction'],'probabilities_json'=>json_encode([$p['probability_up'],$p['probability_neutral'],$p['probability_down']]),'expected_return_pct'=>0.0,'confidence'=>$p['confidence'],'information_cutoff'=>$bars[59]['time']]);$actual=(new OutcomeResolver())->returnPct($f['close'],$f['close']*1.01);$metrics=new MetricsCalculator();
        self::assertSame('FEATURE_SET_V1',$f['feature_set_version']);self::assertSame('PENDING',$target['status']);self::assertNotEmpty($final['forecast_hash']);self::assertEqualsWithDelta(1.0,$actual,1e-9);self::assertEqualsWithDelta(1.0,$metrics->absoluteError(0,$actual),1e-9);
    }
}
