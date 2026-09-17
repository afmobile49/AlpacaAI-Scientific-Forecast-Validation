<?php
declare(strict_types=1);
namespace MarketForecast\MarketData;
use DateTimeImmutable;
interface MarketDataProviderInterface
{
    public function getHistoricalBars(string $symbol, string $timeframe, DateTimeImmutable $start, DateTimeImmutable $end): array;
    public function getLatestValidatedPrice(string $symbol, DateTimeImmutable $notAfter): array;
    public function healthCheck(): bool;
}
