<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\Forecast\QuantForecastEngine;
use PHPUnit\Framework\TestCase;
final class QuantForecastTest extends TestCase { public function testBullishIsDeterministic():void{$f=['ema20'=>12,'ema50'=>10,'close'=>12,'sma20'=>10,'rsi14'=>60,'momentum5'=>1,'momentum20'=>2];$e=new QuantForecastEngine();$a=$e->predict($f);self::assertSame('BULLISH',$a['direction']);self::assertSame('QUANT_V1',$a['algorithm_version']);self::assertSame($a,$e->predict($f));} public function testNeutralForMixedSignals():void{$a=(new QuantForecastEngine())->predict(['ema20'=>10,'ema50'=>10,'close'=>10,'sma20'=>10,'rsi14'=>50,'momentum5'=>0,'momentum20'=>0]);self::assertSame('NEUTRAL',$a['direction']);} }
