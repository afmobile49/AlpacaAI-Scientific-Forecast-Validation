<?php
declare(strict_types=1);
namespace MarketForecast\Evaluation;
use DateTimeImmutable;
use PDO;

final class ForecastTargetRepository
{
    public function __construct(private readonly PDO $pdo, private readonly ForecastTargetFactory $factory = new ForecastTargetFactory()) {}
 public function create(int $predictionId, string $assetType, DateTimeImmutable $referenceTime, string $horizon = '1D'): int
    {
        $target = $this->factory->create($assetType, $referenceTime, $horizon);
  $s=$this->pdo->prepare('SELECT id,target_time,target_rule,status FROM forecast_targets WHERE final_prediction_id=? AND horizon_code=? AND target_rule=?');$s->execute([$predictionId,$horizon,$target['target_rule']]);$existing=$s->fetch(PDO::FETCH_ASSOC);
  if($existing){if($existing['target_time']!==$target['target_time']||$existing['status']!==$target['status'])throw new \RuntimeException('Conflicting target identity.');return (int)$existing['id'];}
  $hasReference=array_filter($this->pdo->query("PRAGMA table_info('forecast_targets')")->fetchAll(PDO::FETCH_ASSOC),static fn($c)=>$c['name']==='reference_rule')!==[];$sql=$hasReference?'INSERT INTO forecast_targets(final_prediction_id,horizon_code,target_time,target_rule,status,reference_rule,reference_status) VALUES(?,?,?,?,?,?,?)':'INSERT INTO forecast_targets(final_prediction_id,horizon_code,target_time,target_rule,status) VALUES(?,?,?,?,?)';$s=$this->pdo->prepare($sql);$values=[$predictionId,$horizon,$target['target_time'],$target['target_rule'],$target['status']];if($hasReference)$values[] = strtoupper($assetType)==='STOCK'?ReferencePriceRuleV2::STOCK:ReferencePriceRuleV2::CRYPTO;if($hasReference)$values[]='REFERENCE_PENDING';$s->execute($values);return (int)$this->pdo->lastInsertId();
    }
}
