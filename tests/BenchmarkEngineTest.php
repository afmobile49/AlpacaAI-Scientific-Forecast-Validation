<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\Evaluation\BenchmarkEngine;use PHPUnit\Framework\TestCase;
final class BenchmarkEngineTest extends TestCase { public function testBenchmarksAreDeterministic():void{$b=new BenchmarkEngine();$f=['momentum5'=>2,'momentum20'=>-1,'sma20'=>105,'sma50'=>100];self::assertSame('BULLISH',$b->predict('ALWAYS_BULLISH',$f));self::assertSame('BULLISH',$b->predict('MOMENTUM_5',$f));self::assertSame('BEARISH',$b->predict('MOMENTUM_20',$f));self::assertSame('BULLISH',$b->predict('SMA20_VS_SMA50',$f));self::assertSame('NEUTRAL',$b->predict('UNKNOWN',$f));} }
