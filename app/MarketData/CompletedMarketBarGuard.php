<?php
declare(strict_types=1);

namespace MarketForecast\MarketData;

use DateTimeImmutable;
use DateTimeZone;

final class CompletedMarketBarGuard
{
    private readonly MarketCalendarService $calendar;

    public function __construct(?MarketCalendarService $calendar = null)
    {
        $this->calendar = $calendar ?? new MarketCalendarService();
    }

    public function assertAllowed(array $bar, DateTimeImmutable $cutoff, string $assetType = 'STOCK'): void
    {
        if (!isset($bar['time'])) {
            throw new DataLeakageException('Market bar timestamp is missing.');
        }

        $time = new DateTimeImmutable((string) $bar['time']);
        if ($time > $cutoff) {
            throw new DataLeakageException('Market bar is after information cutoff.');
        }

        if ($assetType !== 'STOCK') {
            return;
        }

        $ny = new DateTimeZone('America/New_York');
        if ($time->setTimezone($ny)->format('Y-m-d') !== $cutoff->setTimezone($ny)->format('Y-m-d')) {
            return;
        }

        if (!$this->calendar->isSessionComplete($cutoff, $time)) {
            throw new DataLeakageException('Current incomplete stock session is not allowed.');
        }
    }
}
