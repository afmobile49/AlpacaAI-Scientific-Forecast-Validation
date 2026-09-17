<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\Indicators\{SMA,EMA,RSI,Momentum,Volatility,ATR};
use PHPUnit\Framework\TestCase;
final class IndicatorsTest extends TestCase { public function testCore():void{$v=[1,2,3,4,5];self::assertSame(4.0,(new SMA(3))->calculate($v));self::assertEquals(4.0,(new EMA(3))->calculate($v));self::assertSame(1.0,(new Momentum(1))->calculate($v));self::assertSame(100.0,(new RSI(3))->calculate($v));self::assertTrue(abs((new Volatility(3))->calculate($v)-0.0754830476)<1e-9);} public function testHistory():void{self::assertNull((new SMA(20))->calculate([1,2]));} public function testAtr():void{$b=[['high'=>11,'low'=>9,'close'=>10],['high'=>13,'low'=>10,'close'=>12],['high'=>14,'low'=>11,'close'=>13]];self::assertEquals(3.0,(new ATR(2))->calculateFromBars($b));} }
