<?php
declare(strict_types=1);

namespace MarketForecast\Database;

use PDO;

final class Migrator
{
    public function __construct(private readonly PDO $pdo) {}

    public function ensureMigrationsTable(): void
    {
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations (version TEXT PRIMARY KEY, applied_at TEXT NOT NULL)');
    }

    public function apply(string $directory, string $now): int
    {
        $this->ensureMigrationsTable();
        $applied = 0;
        foreach (glob(rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.sql') ?: [] as $file) {
            $version = basename($file);
            $check = $this->pdo->prepare('SELECT 1 FROM schema_migrations WHERE version = :version');
            $check->execute(['version' => $version]);
            if ($check->fetchColumn()) continue;
            // SQLite ignores PRAGMA foreign_keys changes made inside a transaction.
            // Rebuild-style migrations therefore need FK checks disabled before BEGIN.
            $this->pdo->exec('PRAGMA foreign_keys=OFF');
            $this->pdo->beginTransaction();
            try {
                $this->pdo->exec((string) file_get_contents($file));
                $insert = $this->pdo->prepare('INSERT INTO schema_migrations(version, applied_at) VALUES (:version, :applied_at)');
                $insert->execute(['version' => $version, 'applied_at' => $now]);
                $this->pdo->commit();
                $applied++;
                $this->pdo->exec('PRAGMA foreign_keys=ON');
            } catch (\Throwable $e) {
                $this->pdo->rollBack();
                $this->pdo->exec('PRAGMA foreign_keys=ON');
                throw $e;
            }
        }
        return $applied;
    }
}
