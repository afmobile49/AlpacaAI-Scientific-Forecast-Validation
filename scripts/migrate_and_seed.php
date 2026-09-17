<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/Database/Connection.php';
require dirname(__DIR__) . '/app/Database/Migrator.php';
require dirname(__DIR__) . '/app/Database/Seeder.php';

use MarketForecast\Database\Connection;
use MarketForecast\Database\Migrator;
use MarketForecast\Database\Seeder;

$databasePath = $argv[1] ?? throw new InvalidArgumentException('Database path is required.');
$pdo = Connection::open($databasePath);
$migrator = new Migrator($pdo);
$applied = $migrator->apply(dirname(__DIR__) . '/database/migrations', gmdate('c'));
$seeded = (new Seeder($pdo))->seedAssets();
printf("migrations=%d assets_inserted=%d assets_total=%d\n", $applied, $seeded, (int) $pdo->query('SELECT COUNT(*) FROM assets')->fetchColumn());
