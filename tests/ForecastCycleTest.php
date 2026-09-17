<?php
declare(strict_types=1);

namespace MarketForecast\Tests;

use MarketForecast\Forecast\ForecastCycle;
use PHPUnit\Framework\TestCase;

final class ForecastCycleTest extends TestCase
{
    public function testStockAndCryptoUseDistinctAssetTypesAndRunKeys(): void
    {
        self::assertSame('STOCK', ForecastCycle::fromArgument('stock')->assetType());
        self::assertSame('stock-2026-09-15', ForecastCycle::fromArgument('stock')->runKey('2026-09-15'));
        self::assertSame('CRYPTO', ForecastCycle::fromArgument('crypto')->assetType());
        self::assertSame('crypto-2026-09-15', ForecastCycle::fromArgument('crypto')->runKey('2026-09-15'));
    }

    public function testUnknownCycleIsRejectedBeforeDataFetch(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ForecastCycle::fromArgument('weekend-stock');
    }
}
