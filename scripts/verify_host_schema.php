<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/Database/Connection.php';
require dirname(__DIR__).'/app/Database/Migrator.php';
$pdo=MarketForecast\Database\Connection::open(dirname(__DIR__).'/database/forecast.sqlite');
$versions=$pdo->query('SELECT version FROM schema_migrations ORDER BY version')->fetchAll(PDO::FETCH_COLUMN);
$columns=$pdo->query("PRAGMA table_info('evaluation_results')")->fetchAll(PDO::FETCH_ASSOC);
$fk=$pdo->query('PRAGMA foreign_key_check')->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['versions'=>$versions,'evaluation_columns'=>$columns,'foreign_key_check'=>$fk],JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES).PHP_EOL;
