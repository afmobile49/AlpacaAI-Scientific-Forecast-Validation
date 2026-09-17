<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use MarketForecast\Forecast\ReferencePrice;
use PHPUnit\Framework\TestCase;

final class ReferencePriceTest extends TestCase
{
    public function testPositivePriceIsAccepted(): void { ReferencePrice::validate(100.5,'2026-01-01T12:00:00Z'); self::assertTrue(true); }
    public function testNonPositivePriceIsRejected(): void { $this->expectException(\InvalidArgumentException::class); ReferencePrice::validate(0,'2026-01-01T12:00:00Z'); }
}
