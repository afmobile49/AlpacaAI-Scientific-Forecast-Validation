<?php
declare(strict_types=1);

namespace MarketForecast\Tests;

use DateTimeZone;
use MarketForecast\Database\Connection;
use MarketForecast\Database\Migrator;
use MarketForecast\Support\Clock;
use PHPUnit\Framework\TestCase;

final class FoundationTest extends TestCase
{
    public function testClockUsesUtcByDefault(): void
    {
        self::assertSame('UTC', (new Clock())->now()->getTimezone()->getName());
    }

    public function testConnectionAppliesRequiredPragmas(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'mf-');
        $pdo = Connection::open($path);
        self::assertSame('1', (string) $pdo->query('PRAGMA foreign_keys')->fetchColumn());
        self::assertSame('5000', (string) $pdo->query('PRAGMA busy_timeout')->fetchColumn());
        @unlink($path);
    }

    public function testMigratorIsIdempotent(): void
    {
        $root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mf-migrations-' . bin2hex(random_bytes(4));
        mkdir($root, 0775, true);
        file_put_contents($root . '/001_test.sql', 'CREATE TABLE foundation_probe (id INTEGER PRIMARY KEY);');
        $pdo = Connection::open($root . '/test.sqlite');
        $migrator = new Migrator($pdo);
        self::assertSame(1, $migrator->apply($root, '2026-01-01T00:00:00+00:00'));
        self::assertSame(0, $migrator->apply($root, '2026-01-01T00:00:00+00:00'));
        self::assertSame(1, (int) $pdo->query("SELECT COUNT(*) FROM schema_migrations")->fetchColumn());
        @unlink($root . '/001_test.sql'); @unlink($root . '/test.sqlite'); @rmdir($root);
    }
}
