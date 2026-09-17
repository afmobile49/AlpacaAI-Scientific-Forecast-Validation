<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use DateTimeImmutable;
use MarketForecast\Evaluation\ForecastTargetFactory;
use PHPUnit\Framework\TestCase;

final class ForecastTargetFactoryTest extends TestCase
{
    public function testCryptoUsesTwentyFourHourTarget(): void
    {
        $r=(new ForecastTargetFactory())->create('CRYPTO', new DateTimeImmutable('2026-01-01T12:00:00Z'));
        self::assertSame('OUTCOME_PRICE_RULE_V1:CRYPTO_24H', $r['target_rule']);
        self::assertSame('2026-01-02T12:00:00+00:00', $r['target_time']);
    }
    public function testStockUsesNextSessionCloseRule(): void
    {
        $r=(new ForecastTargetFactory())->create('STOCK', new DateTimeImmutable('2026-01-02T12:00:00Z'));
        self::assertSame('OUTCOME_PRICE_RULE_V1:STOCK_1D', $r['target_rule']);
        self::assertSame('2026-01-05T21:00:00+00:00', $r['target_time']);
    }
}
