<?php
declare(strict_types=1);
namespace MarketForecast\Forecast;
use DateTimeImmutable;

final class ReferencePrice
{
    public static function validate(float $price, string $time): void
    {
        if (!is_finite($price) || $price <= 0) throw new \InvalidArgumentException('Reference price must be positive and finite.');
        try { new DateTimeImmutable($time); } catch (\Throwable) { throw new \InvalidArgumentException('Invalid reference price time.'); }
    }
}
