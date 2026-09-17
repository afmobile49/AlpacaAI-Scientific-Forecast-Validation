<?php
declare(strict_types=1);

namespace MarketForecast\Forecast;

final class ForecastCycle
{
    private function __construct(private readonly string $name, private readonly string $assetType)
    {
    }

    public static function fromArgument(string $value): self
    {
        return match (strtolower(trim($value))) {
            'stock' => new self('stock', 'STOCK'),
            'crypto' => new self('crypto', 'CRYPTO'),
            default => throw new \InvalidArgumentException('Forecast cycle must be stock or crypto.'),
        };
    }

    public function assetType(): string
    {
        return $this->assetType;
    }

    public function runKey(string $date): string
    {
        return $this->name . '-' . $date;
    }
}
