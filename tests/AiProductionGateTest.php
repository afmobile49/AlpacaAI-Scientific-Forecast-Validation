<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\AI\AiProductionGate;
use PHPUnit\Framework\TestCase;
final class AiProductionGateTest extends TestCase
{
 public function testDisabledByDefault():void{self::assertFalse(AiProductionGate::enabled('false','key'));}
 public function testRequiresCredential():void{self::assertFalse(AiProductionGate::enabled('true',''));}
 public function testEnabledOnlyExplicitly():void{self::assertTrue(AiProductionGate::enabled('true','key'));}
}
