<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\Evaluation\PriceRuleV1;
use PHPUnit\Framework\TestCase;
final class ReferenceCaptureTest extends TestCase
{
 public function testReferenceRuleIsVersionedAndStable():void{self::assertSame('REFERENCE_PRICE_RULE_V1',PriceRuleV1::REFERENCE);self::assertSame(100.0,PriceRuleV1::stockReference(['close'=>100]));}
}
