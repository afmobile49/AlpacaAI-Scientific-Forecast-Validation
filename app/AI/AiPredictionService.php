<?php
declare(strict_types=1);
namespace MarketForecast\AI;
use PDO;
final class AiPredictionService
{
 public function __construct(private readonly PDO $pdo,private readonly OpenAIProvider $provider){}
 public function predict(int $runId,int $assetId,string $horizon,array $features,string $cutoff):?int
 {try{$p=$this->provider->forecast($features,$cutoff);$s=$this->pdo->prepare('INSERT OR IGNORE INTO ai_predictions(run_id,asset_id,horizon_code,direction,probabilities_json,expected_return_pct,confidence,reason_codes_json,explanation,raw_response_hash) VALUES(?,?,?,?,?,?,?,?,?,?)');$probs=[$p['probability_up'],$p['probability_neutral'],$p['probability_down']];$s->execute([$runId,$assetId,$horizon,$p['direction'],json_encode($probs,JSON_THROW_ON_ERROR),$p['expected_return_pct'],$p['confidence'],json_encode($p['reason_codes'],JSON_THROW_ON_ERROR),$p['explanation'],hash('sha256',json_encode($p,JSON_THROW_ON_ERROR))]);return (int)$this->pdo->lastInsertId();}catch(\Throwable $e){$this->pdo->prepare("INSERT INTO system_events(severity,event_type,message,metadata_json,created_at) VALUES('WARNING','AI_PREDICTION_FAILED',?,?,?)")->execute(['AI prediction failed',json_encode(['run_id'=>$runId,'asset_id'=>$assetId,'horizon'=>$horizon]),gmdate('c')]);return null;}}
}
