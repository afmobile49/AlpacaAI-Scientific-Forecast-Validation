<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\AI\{OpenAIProvider,PairedEligibility};
use MarketForecast\Forecast\SnapshotAgreement;
use MarketForecast\Forecast\QuantForecastEngine;
use PHPUnit\Framework\TestCase;
final class PairedAiQuantE2ETest extends TestCase
{
 public function testSameFrozenSnapshotProducesIndependentEligiblePair():void{$features=['close'=>110.0,'sma20'=>108.0,'sma50'=>105.0,'ema20'=>109.0,'ema50'=>106.0,'rsi14'=>60.0,'atr14'=>2.0,'momentum5'=>2.0,'momentum20'=>4.0,'rolling_volatility20'=>.02,'distance_sma20'=>.018,'distance_sma50'=>.047,'volume_ratio20'=>1.1];$cutoff='2026-09-11T13:30:00Z';$hash=SnapshotAgreement::hash($features,$cutoff);$quant=(new QuantForecastEngine())->predict($features);$ai=(new OpenAIProvider('test','test','https://invalid',fn()=>['output_text'=>'{"direction":"BULLISH","probability_up":0.7,"probability_neutral":0.2,"probability_down":0.1,"expected_return_pct":1.2,"confidence":0.7,"reason_codes":["POSITIVE_TREND"],"explanation":"offline"}']))->forecast($features,$cutoff);self::assertSame('BULLISH',$quant['direction']);self::assertSame('BULLISH',$ai['direction']);self::assertSame($hash,SnapshotAgreement::hash($features,$cutoff));self::assertTrue(PairedEligibility::official($cutoff,1,true,true,true,$cutoff,$cutoff));}
}
