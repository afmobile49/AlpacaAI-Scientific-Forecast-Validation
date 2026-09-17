<?php
declare(strict_types=1);
namespace MarketForecast\Tests;
use DateTimeImmutable;
use MarketForecast\Evaluation\TargetTimePriceResolver;
use MarketForecast\MarketData\MarketDataProviderInterface;
use PHPUnit\Framework\TestCase;

final class TargetTimePriceResolverTest extends TestCase
{
    private function provider(array $bars): MarketDataProviderInterface
    {
        return new class($bars) implements MarketDataProviderInterface {
            public function __construct(private array $bars) {}
            public function getHistoricalBars(string $symbol, string $timeframe, DateTimeImmutable $start, DateTimeImmutable $end): array { return $this->bars; }
            public function getLatestValidatedPrice(string $symbol, DateTimeImmutable $notAfter): array { throw new \LogicException('latest price must never be used for outcome resolution'); }
            public function healthCheck(): bool { return true; }
        };
    }

    public function testDelayedStockResolutionUsesHistoricalTargetSession(): void
    {
        $bar = (new TargetTimePriceResolver($this->provider([['time'=>'2026-01-02T21:00:00+00:00','close'=>101,'open'=>100,'high'=>102,'low'=>99,'volume'=>1,'provider'=>'ALPACA']])))->resolve('SPY','STOCK','2026-01-02T21:00:00Z');
        self::assertEquals(101.0, $bar['close']);
    }

    public function testCryptoUsesFirstBarAtOrAfterTarget(): void
    {
        $bar = (new TargetTimePriceResolver($this->provider([
            ['time'=>'2026-01-02T20:59:00+00:00','close'=>99,'open'=>99,'high'=>99,'low'=>99,'volume'=>1,'provider'=>'ALPACA'],
            ['time'=>'2026-01-02T21:01:00+00:00','close'=>101,'open'=>101,'high'=>101,'low'=>101,'volume'=>1,'provider'=>'ALPACA'],
        ])))->resolve('BTC/USD','CRYPTO','2026-01-02T21:00:00Z');
        self::assertEquals(101.0, $bar['close']);
    }

    public function testMissingCryptoTargetBarIsRetryable(): void
    {
        $this->expectException(\RuntimeException::class);
        (new TargetTimePriceResolver($this->provider([['time'=>'2026-01-02T22:01:00+00:00','close'=>101,'open'=>101,'high'=>101,'low'=>101,'volume'=>1,'provider'=>'ALPACA']])))->resolve('BTC/USD','CRYPTO','2026-01-02T21:00:00Z');
    }
}
