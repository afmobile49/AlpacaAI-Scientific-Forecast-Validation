<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\AI\PairedEligibility;
use PHPUnit\Framework\TestCase;
final class PairedEligibilityTest extends TestCase
{
 public function testPreAiIsExcluded():void{self::assertFalse(PairedEligibility::eligible(null,'2026-09-10T00:00:00Z','2026-09-10T00:00:00Z',true));}
 public function testRequiresPostActivationAndSameSnapshot():void{self::assertFalse(PairedEligibility::eligible('2026-09-11T00:00:00Z','2026-09-10T00:00:00Z','2026-09-11T01:00:00Z',true));self::assertFalse(PairedEligibility::eligible('2026-09-11T00:00:00Z','2026-09-11T01:00:00Z','2026-09-11T01:00:00Z',false));self::assertTrue(PairedEligibility::eligible('2026-09-11T00:00:00Z','2026-09-11T01:00:00Z','2026-09-11T01:00:00Z',true));}
 public function testOfficialEligibilityRequiresPairAndBothModels():void{self::assertFalse(PairedEligibility::official('2026-09-11T00:00:00Z',null,true,true,true,'2026-09-11T01:00:00Z','2026-09-11T01:00:00Z'));self::assertFalse(PairedEligibility::official('2026-09-11T00:00:00Z',1,true,false,true,'2026-09-11T01:00:00Z','2026-09-11T01:00:00Z'));self::assertTrue(PairedEligibility::official('2026-09-11T00:00:00Z',1,true,true,true,'2026-09-11T01:00:00Z','2026-09-11T01:00:00Z'));}
}
