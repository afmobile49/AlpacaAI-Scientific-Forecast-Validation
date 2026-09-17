<?php
declare(strict_types=1);
namespace MarketForecast\Reliability;
use PDO;
final class ReliabilityRepository
{
 public function __construct(private readonly PDO $pdo){}
 public function createRun(array $r):?int{$s=$this->pdo->prepare('INSERT OR IGNORE INTO reliability_runs(experiment_id,source_type,window_code,window_start,window_end,calculation_version,status,resolved_n,created_at) VALUES(?,?,?,?,?,?,?,?,?)');$s->execute([$r['experiment_id'],$r['source_type'],$r['window_code'],$r['window_start']??null,$r['window_end'],$r['calculation_version'],$r['status'],$r['resolved_n'],$r['created_at']]);if($s->rowCount()===0)return null;return (int)$this->pdo->lastInsertId();}
 public function saveScore(int $runId,array $s):int{$q=$this->pdo->prepare('INSERT INTO reliability_scores(reliability_run_id,fps,ecs,frs,fps_label,ecs_label,frs_label,diagnostics_json,created_at) VALUES(?,?,?,?,?,?,?,?,?)');$q->execute([$runId,$s['fps']??null,$s['ecs']??null,$s['frs']??null,$s['fps_label']??null,$s['ecs_label']??null,$s['frs_label']??null,json_encode($s['diagnostics']??[],JSON_THROW_ON_ERROR),$s['created_at']]);return (int)$this->pdo->lastInsertId();}
}
