<?php
declare(strict_types=1);
namespace MarketForecast\Forecast;
use PDO;use MarketForecast\Support\CanonicalHasher;
final class SnapshotPersistence
{
    public function __construct(private readonly PDO $pdo) {}

    /** @return array{id:int,feature_snapshot_hash:string,source_data_hash:string} */
    public function save(int $experimentId, int $assetId, string $cutoff, array $features, array $bars): array
    {
        $marketHash = CanonicalHasher::hash(array_map(
            static fn(array $bar) => [$bar['time'], $bar['open'], $bar['high'], $bar['low'], $bar['close'], $bar['volume'], $bar['provider']],
            $bars
        ));
        $featureHash = CanonicalHasher::hash($features);
        $insert = $this->pdo->prepare('INSERT OR IGNORE INTO market_snapshots(experiment_id,asset_id,information_cutoff,snapshot_time,features_json,source_data_hash,feature_set_version,feature_snapshot_hash,market_data_fingerprint,market_bar_count) VALUES(?,?,?,?,?,?,?,?,?,?)');
        $insert->execute([$experimentId, $assetId, $cutoff, gmdate('c'), json_encode($features, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES), $marketHash, $features['feature_set_version'] ?? 'FEATURE_SET_V1', $featureHash, $marketHash, count($bars)]);
        $select = $this->pdo->prepare('SELECT id,feature_snapshot_hash,source_data_hash FROM market_snapshots WHERE experiment_id=? AND asset_id=? AND information_cutoff=?');
        $select->execute([$experimentId, $assetId, $cutoff]);
        $snapshot = $select->fetch(PDO::FETCH_ASSOC);
        if (!$snapshot) {
            throw new \RuntimeException('Unable to persist the immutable market snapshot.');
        }
        if ((string) $snapshot['feature_snapshot_hash'] !== $featureHash || (string) $snapshot['source_data_hash'] !== $marketHash) {
            throw new \RuntimeException('Immutable snapshot identity conflict.');
        }
        return ['id' => (int) $snapshot['id'], 'feature_snapshot_hash' => $featureHash, 'source_data_hash' => $marketHash];
    }

    public function saveQuantScore(int $id, float $score, string $version, string $now): void
    {
        $statement = $this->pdo->prepare('INSERT OR IGNORE INTO quant_scores(quant_prediction_id,quant_score,algorithm_version,created_at) VALUES(?,?,?,?)');
        $statement->execute([$id, $score, $version, $now]);
    }
}
