<?php
declare(strict_types=1);

namespace MarketForecast\Tests;

use MarketForecast\Database\Connection;
use MarketForecast\Database\Migrator;
use MarketForecast\Database\Seeder;
use PHPUnit\Framework\TestCase;

final class DatabaseTest extends TestCase
{
    public function testMigrationAndAssetSeedAreIdempotent(): void
    {
        $dir = dirname(__DIR__); $path = tempnam(sys_get_temp_dir(), 'mf-db-');
        $pdo = Connection::open($path); $migrator = new Migrator($pdo);
        self::assertSame(13, $migrator->apply($dir . '/database/migrations', '2026-01-01T00:00:00+00:00'));
        self::assertSame(0, $migrator->apply($dir . '/database/migrations', '2026-01-01T00:00:00+00:00'));
        $seeder = new Seeder($pdo); self::assertSame(8, $seeder->seedAssets()); self::assertSame(0, $seeder->seedAssets());
        self::assertSame(8, (int) $pdo->query('SELECT COUNT(*) FROM assets')->fetchColumn());
        self::assertSame(1, (int) $pdo->query("SELECT COUNT(*) FROM assets WHERE symbol='XAUT/USD' AND provider='XAUT_PROVIDER'")->fetchColumn());
        @unlink($path); @unlink($path . '-wal'); @unlink($path . '-shm');
    }
}



