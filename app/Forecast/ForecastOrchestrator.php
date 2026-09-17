<?php
declare(strict_types=1);
namespace MarketForecast\Forecast;
use MarketForecast\Jobs\{JobLock,JobState};
use PDO;
final class ForecastOrchestrator
{
 public function __construct(private readonly PDO $pdo,private readonly JobLock $lock,private readonly QuantForecastEngine $quant,private readonly ForecastFreezer $freezer){}
 public function start(int $experimentId,string $runKey,string $scheduledFor,string $now):?int{if(!$this->lock->acquire($runKey,'forecast',$now))return null;$s=$this->pdo->prepare('INSERT INTO forecast_runs(experiment_id,run_key,scheduled_for,started_at,status,state_detail) VALUES(?,?,?,?,?,?)');$s->execute([$experimentId,$runKey,$scheduledFor,$now,'RUNNING',JobState::CREATED]);return (int)$this->pdo->lastInsertId();}
 public function markState(int $runId,string $state,string $now):void{$allowed=[JobState::CREATED,JobState::MARKET_DATA_READY,JobState::FEATURES_READY,JobState::QUANT_READY,JobState::AI_READY,JobState::VALIDATED,JobState::FINALIZED,JobState::FAILED];if(!in_array($state,$allowed,true))throw new \InvalidArgumentException('Invalid forecast state.');$s=$this->pdo->prepare('UPDATE forecast_runs SET state_detail=?,status=?,completed_at=CASE WHEN ? IN (?,?) THEN ? ELSE completed_at END WHERE id=?');$s->execute([$state,$state,$state,JobState::FINALIZED,JobState::FAILED,$now,$runId]);}
}
