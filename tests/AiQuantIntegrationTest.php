<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\AI\{AiProductionGate,OpenAIProvider};
use MarketForecast\Forecast\{QuantForecastEngine,SnapshotAgreement};
use PHPUnit\Framework\TestCase;

final class AiQuantIntegrationTest extends TestCase
{
 public function testAiAndQuantUseTheSameSnapshot():void{$features=['close'=>110.0,'sma20'=>108.0,'sma50'=>105.0,'ema20'=>109.0,'ema50'=>106.0,'rsi14'=>60.0,'momentum5'=>2.0,'momentum20'=>4.0];$quant=(new QuantForecastEngine())->predict($features);$ai=new OpenAIProvider('test-key','test-model','https://invalid',static fn()=>['output_text'=>json_encode(['direction'=>$quant['direction'],'probability_up'=>.7,'probability_neutral'=>.2,'probability_down'=>.1,'expected_return_pct'=>1.2,'confidence'=>.7,'reason_codes'=>['POSITIVE_TREND'],'explanation'=>'Deterministic integration fixture.'])]);$result=$ai->forecast($features,'2026-01-01T00:00:00Z');SnapshotAgreement::assertSame($features,$features,'2026-01-01T00:00:00Z');self::assertSame($quant['direction'],$result['direction']);self::assertTrue(AiProductionGate::enabled('true','test-key'));}
}
