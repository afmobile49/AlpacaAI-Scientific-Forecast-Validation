<?php
declare(strict_types=1);
namespace MarketForecast\MarketData;
use DateTimeImmutable;
use RuntimeException;
final class XautProvider implements MarketDataProviderInterface
{
    public function __construct(private readonly ?string $baseUrl = null, private readonly ?string $apiKey = null) {}
    public function getHistoricalBars(string $symbol, string $timeframe, DateTimeImmutable $start, DateTimeImmutable $end): array { throw new RuntimeException('XAUT provider is not configured.'); }
    public function getLatestValidatedPrice(string $symbol, DateTimeImmutable $notAfter): array { throw new RuntimeException('XAUT provider is not configured.'); }
    public function healthCheck(): bool { return false; }
}
