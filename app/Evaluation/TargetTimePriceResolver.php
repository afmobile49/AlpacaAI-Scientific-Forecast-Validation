<?php
declare(strict_types=1);

namespace MarketForecast\Evaluation;

use DateTimeImmutable;
use DateTimeZone;
use MarketForecast\MarketData\MarketDataProviderInterface;

final class TargetTimePriceResolver
{
    public function __construct(private readonly MarketDataProviderInterface $provider) {}

    /**
     * Resolves only an historical target bar. It never asks the provider for the latest price.
     * Stock: close of the scheduled target session. Crypto: first 1-minute bar at/after t1,
     * bounded by the immutable 60-minute tolerance.
     */
    public function resolve(string $symbol, string $assetType, string $targetTime): array
    {
        $target = new DateTimeImmutable($targetTime);
        if (strtoupper($assetType) === 'STOCK') {
            $bars = $this->provider->getHistoricalBars($symbol, '1Day', $target->modify('-1 day'), $target->modify('+1 day'));
            $targetDate = $target->setTimezone(new DateTimeZone('America/New_York'))->format('Y-m-d');
            foreach ($bars as $bar) {
                if ((new DateTimeImmutable($bar['time']))->setTimezone(new DateTimeZone('America/New_York'))->format('Y-m-d') === $targetDate) return $bar;
            }
            throw new \RuntimeException('Target stock session bar unavailable; retryable.');
        }
        $bars = $this->provider->getHistoricalBars($symbol, '1Min', $target, $target->modify('+60 minutes'));
        foreach ($bars as $bar) {
            $at = new DateTimeImmutable($bar['time']);
            if ($at >= $target && $at <= $target->modify('+60 minutes')) return $bar;
        }
        throw new \RuntimeException('Target crypto bar unavailable within tolerance; retryable.');
    }
}
