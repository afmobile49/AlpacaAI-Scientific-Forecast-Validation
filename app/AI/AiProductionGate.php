<?php
declare(strict_types=1);

namespace MarketForecast\AI;

use PDO;

final class AiProductionGate
{
    public static function enabled(string $flag, string $apiKey): bool { return strtolower(trim($flag)) === 'true' && trim($apiKey) !== ''; }

    /** @return array<string,bool|array> */
    public static function runtimeReadiness(PDO $pdo, string $flag, string $apiKey, string $referenceRule = 'REFERENCE_PRICE_RULE_V2'): array
    {
        $tables = ['experiments','forecast_runs','market_snapshots','quant_predictions','ai_predictions','final_predictions','forecast_targets','actual_outcomes','evaluation_results','benchmark_results','reliability_runs','reliability_scores','system_jobs','forecast_pairs','system_events'];
        $missing = [];
        foreach ($tables as $table) {
            $q = $pdo->prepare("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name=?");
            $q->execute([$table]);
            if ((int) $q->fetchColumn() !== 1) $missing[] = $table;
        }
        $columns = static function (string $table) use ($pdo): array { return array_column($pdo->query("PRAGMA table_info('$table')")->fetchAll(PDO::FETCH_ASSOC), 'name'); };
        $has = static function (array $available, array $required): bool { return $required === array_values(array_intersect($required, $available)); };
        $runColumns = $columns('forecast_runs');
        $quantColumns = $columns('quant_predictions');
        $aiColumns = $columns('ai_predictions');
        $finalColumns = $columns('final_predictions');
        $targetColumns = $columns('forecast_targets');
        return [
            'enabled' => self::enabled($flag, $apiKey),
            'schema_ready' => $missing === [],
            'missing_tables' => $missing,
            'cutoff_ready' => $has($runColumns, ['information_cutoff']),
            'snapshot_ready' => $has($quantColumns, ['information_cutoff','market_snapshot_id','feature_snapshot_hash']) && $has($aiColumns, ['information_cutoff','market_snapshot_id','feature_snapshot_hash']),
            'pairing_ready' => $has($finalColumns, ['forecast_pair_id']) && $missing === [],
            'target_lifecycle_ready' => $has($targetColumns, ['reference_price','reference_price_time','reference_rule','reference_status']),
            'evaluation_ready' => !in_array('evaluation_results', $missing, true),
            'benchmark_ready' => !in_array('benchmark_results', $missing, true),
            'rolling_frf_ready' => !in_array('reliability_runs', $missing, true) && !in_array('reliability_scores', $missing, true),
            'pre_ai_filter_ready' => class_exists(PairedStatistics::class),
            'reference_rule_ready' => $referenceRule === 'REFERENCE_PRICE_RULE_V2',
        ];
    }

    public static function assertRuntimeReady(PDO $pdo, string $flag, string $apiKey, ?string $referenceRule = null): void
    {
        $readiness = self::runtimeReadiness($pdo, $flag, $apiKey, $referenceRule ?? 'REFERENCE_PRICE_RULE_V2');
        foreach (['enabled','schema_ready','cutoff_ready','snapshot_ready','pairing_ready','target_lifecycle_ready','evaluation_ready','benchmark_ready','rolling_frf_ready','pre_ai_filter_ready','reference_rule_ready'] as $key) {
            if ($readiness[$key] !== true) throw new \RuntimeException('AI runtime gate failed: ' . $key);
        }
    }
}
