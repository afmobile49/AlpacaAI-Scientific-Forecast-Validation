<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use DateTimeImmutable;
use MarketForecast\Evaluation\PriceRuleV1;
use PHPUnit\Framework\TestCase;
final class PriceRuleV1Test extends TestCase
{
 public function testStocksUseDailyCloseForReferenceAndOutcome():void{$b=['time'=>'2026-07-03T17:00:00+00:00','close'=>101.25];self::assertSame(101.25,PriceRuleV1::stockReference($b));self::assertSame(101.25,PriceRuleV1::stockOutcome($b));}
 public function testEarlyCloseStillUsesThatSessionClose():void{self::assertSame(99.5,PriceRuleV1::stockOutcome(['time'=>'2026-11-27T18:00:00+00:00','close'=>99.5]));}
 public function testCryptoUsesFirstOneMinuteBarAtOrAfterTargetWithinSixtyMinutes():void{$t=new DateTimeImmutable('2026-01-02T12:00:00Z');$b=[['time'=>'2026-01-02T12:00:30Z','close'=>202.5],['time'=>'2026-01-02T12:01:00Z','close'=>203]];self::assertSame(202.5,PriceRuleV1::cryptoOutcome($b,$t));}
 public function testMissingCryptoBarIsRetryable():void{$this->expectException(\RuntimeException::class);PriceRuleV1::cryptoOutcome([['time'=>'2026-01-02T14:00:00Z','close'=>1]],new DateTimeImmutable('2026-01-02T12:00:00Z'));}
 public function testCryptoTargetTimestampsAreExact():void{self::assertSame('2026-01-02T12:00:00+00:00',PriceRuleV1::cryptoTarget(new DateTimeImmutable('2026-01-01T12:00:00Z'),'24H')->format('c'));self::assertSame('2026-01-08T12:00:00+00:00',PriceRuleV1::cryptoTarget(new DateTimeImmutable('2026-01-01T12:00:00Z'),'7D')->format('c'));}
}
