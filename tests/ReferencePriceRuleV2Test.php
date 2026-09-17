<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use DateTimeImmutable;
use MarketForecast\Evaluation\ReferencePriceRuleV2;
use PHPUnit\Framework\TestCase;
final class ReferencePriceRuleV2Test extends TestCase
{
 public function testStockUsesNextRegularSessionOpen():void{self::assertSame(103.5,ReferencePriceRuleV2::stockReference(['time'=>'2026-09-14T13:30:00Z','open'=>103.5,'close'=>105]));}
 public function testCryptoUsesT0BarNotFutureTarget():void{self::assertSame(200.25,ReferencePriceRuleV2::cryptoReference([['time'=>'2026-09-10T12:00:30Z','close'=>200.25],['time'=>'2026-09-17T12:00:00Z','close'=>999]],new DateTimeImmutable('2026-09-10T12:00:00Z')));}
 public function testMissingCryptoReferenceIsRetryable():void{$this->expectException(\RuntimeException::class);ReferencePriceRuleV2::cryptoReference([['time'=>'2026-09-10T14:00:00Z','close'=>1]],new DateTimeImmutable('2026-09-10T12:00:00Z'));}
}
