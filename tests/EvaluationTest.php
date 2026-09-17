<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\Evaluation\{MetricsCalculator,OutcomeResolver};use PHPUnit\Framework\TestCase;
final class EvaluationTest extends TestCase { public function testMetrics():void{$m=new MetricsCalculator();self::assertTrue($m->directionCorrect('BULLISH','BULLISH'));self::assertSame(1.5,$m->absoluteError(2,0.5));self::assertSame(2.25,$m->squaredError(2,.5));self::assertTrue(abs($m->brier(.7,.2,.1,'BULLISH')-.14)<1e-12);self::assertEquals(sqrt(2.5),$m->rmse([1,4]));} public function testOutcome():void{$o=new OutcomeResolver();self::assertSame('BULLISH',$o->direction(2));self::assertSame('NEUTRAL',$o->direction(.1,.2));self::assertSame('NEUTRAL',$o->direction(.5));self::assertSame('BEARISH',$o->direction(-.51));self::assertSame(10.0,$o->returnPct(100,110));} }
