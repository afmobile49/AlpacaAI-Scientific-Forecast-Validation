<?php
declare(strict_types=1);

namespace MarketForecast\Reports;

use MarketForecast\AI\PairedStatistics;
use PDO;

final class DailyReportDataBuilder
{
    public function __construct(private readonly PDO $pdo) {}

    public function build(string $date, ?string $runKey = null): array
    {
        $runId = $this->runId($runKey);
        if ($runKey !== null && $runId === null) return $this->empty($date);
        [$join, $where, $params] = $this->scope($date, $runId);

        $summaryQuery = $this->pdo->prepare("SELECT COUNT(*) n, COALESCE(SUM(ft.status='RESOLVED'),0) resolved FROM final_predictions fp $join LEFT JOIN forecast_targets ft ON ft.final_prediction_id=fp.id WHERE $where");
        $summaryQuery->execute($params);
        $summary = $summaryQuery->fetch(PDO::FETCH_ASSOC) ?: ['n'=>0, 'resolved'=>0];

        $forecastQuery = $this->pdo->prepare("SELECT fp.id,fp.source_type,fp.forecast_pair_id,a.symbol,a.asset_type,fp.horizon_code,fp.direction,fp.expected_return_pct,fp.confidence,fp.status,ao.actual_return_pct,ao.actual_direction,er.direction_correct FROM final_predictions fp $join JOIN assets a ON a.id=fp.asset_id LEFT JOIN actual_outcomes ao ON ao.final_prediction_id=fp.id LEFT JOIN evaluation_results er ON er.final_prediction_id=fp.id WHERE $where ORDER BY fp.confidence DESC,a.symbol,fp.source_type");
        $forecastQuery->execute($params);
        $forecasts = $forecastQuery->fetchAll(PDO::FETCH_ASSOC);

        $reliability = $this->reliability();
        $benchmarkQuery = $this->pdo->prepare('SELECT benchmark_code,COUNT(*) samples,AVG(correct)*100 accuracy FROM benchmark_results WHERE forecast_date=? GROUP BY benchmark_code ORDER BY benchmark_code');
        $benchmarkQuery->execute([$date]);
        $experimentId = (int)($this->pdo->query("SELECT id FROM experiments WHERE experiment_code='EXP-001' LIMIT 1")->fetchColumn() ?: 0);
        $paired = $experimentId > 0 ? (new PairedStatistics($this->pdo))->summary($experimentId, $runId) : $this->emptyPaired();

        return [
            'report_date'=>$date,
            'summary'=>array_merge(['forecast_count'=>(int)$summary['n'], 'resolved_count'=>(int)$summary['resolved']], $paired),
            'forecasts'=>$forecasts,
            'outcomes'=>array_values(array_filter($forecasts, static fn(array $row): bool => $row['actual_direction'] !== null)),
            'reliability'=>$reliability['primary'],
            'reliability_windows'=>$reliability['windows'],
            'benchmarks'=>$benchmarkQuery->fetchAll(PDO::FETCH_ASSOC),
            'health'=>['status'=>'ok','trading_enabled'=>false,'paper_trading_enabled'=>false],
        ];
    }

    private function scope(string $date, ?int $runId): array
    {
        if ($runId === null) return ['', 'substr(fp.generated_at,1,10)=?', [$date]];
        return ['', 'fp.run_id=?', [$runId]];
    }

    private function runId(?string $runKey): ?int
    {
        if ($runKey === null) return null;
        $query = $this->pdo->prepare('SELECT id FROM forecast_runs WHERE run_key=? LIMIT 1');
        $query->execute([$runKey]);
        $id = $query->fetchColumn();
        return $id === false ? null : (int)$id;
    }

    private function reliability(): array
    {
        $query = $this->pdo->query("SELECT rs.fps,rs.ecs,rs.frs,rs.fps_label,rs.ecs_label,rs.frs_label,rr.window_code,rr.resolved_n,rr.window_end FROM reliability_scores rs JOIN reliability_runs rr ON rr.id=rs.reliability_run_id WHERE rr.source_type='QUANT' ORDER BY rr.window_end DESC");
        $windows = [];
        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row) if (!isset($windows[$row['window_code']])) $windows[$row['window_code']] = $row;
        return ['primary'=>$windows['CUMULATIVE'] ?? (reset($windows) ?: []), 'windows'=>$windows];
    }

    private function empty(string $date): array
    {
        return ['report_date'=>$date, 'summary'=>array_merge(['forecast_count'=>0, 'resolved_count'=>0], $this->emptyPaired()), 'forecasts'=>[], 'outcomes'=>[], 'reliability'=>[], 'reliability_windows'=>[], 'benchmarks'=>[], 'health'=>['status'=>'ok','trading_enabled'=>false,'paper_trading_enabled'=>false]];
    }

    private function emptyPaired(): array
    {
        return ['paired_eligible_count'=>0,'paired_resolved_count'=>0,'quant_accuracy_pct'=>null,'ai_accuracy_pct'=>null,'ai_edge_pp'=>null];
    }
}
