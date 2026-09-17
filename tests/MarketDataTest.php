<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use DateTimeImmutable;
use MarketForecast\MarketData\XautProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
final class MarketDataTest extends TestCase
{
    public function testUnconfiguredXautFailsClosed(): void { $this->expectException(RuntimeException::class); (new XautProvider())->getLatestValidatedPrice('XAUT/USD', new DateTimeImmutable()); }
}
