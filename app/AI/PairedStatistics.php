<?php
declare(strict_types=1);

namespace MarketForecast\AI;

use PDO;

final class PairedStatistics
{
    public function __construct(private readonly PDO $pdo) {}

    /** @return array{paired_eligible_count:int,paired_resolved_count:int,quant_accuracy_pct:?float,ai_accuracy_pct:?float,ai_edge_pp:?float} */
    public function summary(int $experimentId, ?int $runId = null): array
    {
        $joins = " FROM forecast_pairs p
            JOIN experiments e ON e.id=p.experiment_id
            JOIN final_predictions q ON q.forecast_pair_id=p.id AND q.source_type='QUANT'
            JOIN final_predictions a ON a.forecast_pair_id=p.id AND a.source_type='AI'";
        $filter = " WHERE p.experiment_id=? AND e.ai_activation_at IS NOT NULL
              AND q.generated_at>=e.ai_activation_at AND a.generated_at>=e.ai_activation_at
              AND q.forecast_hash<>'' AND a.forecast_hash<>''";
        $params = [$experimentId];
        if ($runId !== null) {
            $filter .= ' AND q.run_id=? AND a.run_id=?';
            $params[] = $runId;
            $params[] = $runId;
        }
        $eligible = $this->pdo->prepare('SELECT COUNT(*)' . $joins . $filter);
        $eligible->execute($params);
        $resolved = $this->pdo->prepare('SELECT COUNT(*) n,AVG(eq.direction_correct)*100 quant_accuracy,AVG(ea.direction_correct)*100 ai_accuracy' . $joins . ' JOIN evaluation_results eq ON eq.final_prediction_id=q.id JOIN evaluation_results ea ON ea.final_prediction_id=a.id' . $filter);
        $resolved->execute($params);
        $row = $resolved->fetch(PDO::FETCH_ASSOC) ?: [];
        $quant = $row['quant_accuracy'] === null ? null : (float) $row['quant_accuracy'];
        $ai = $row['ai_accuracy'] === null ? null : (float) $row['ai_accuracy'];
        return ['paired_eligible_count' => (int) $eligible->fetchColumn(), 'paired_resolved_count' => (int) ($row['n'] ?? 0), 'quant_accuracy_pct' => $quant, 'ai_accuracy_pct' => $ai, 'ai_edge_pp' => $quant === null || $ai === null ? null : $ai - $quant];
    }

    public function eligibleCount(int $experimentId): int { return $this->summary($experimentId)['paired_eligible_count']; }
}
