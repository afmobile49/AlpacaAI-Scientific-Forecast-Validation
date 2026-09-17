<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
use MarketForecast\Database\Connection;
use MarketForecast\Evaluation\MetricsCalculator;
$db=$argv[1]??dirname(__DIR__).'/database/forecast.sqlite';$pdo=Connection::open($db);$rows=$pdo->query("SELECT fp.id,fp.direction,fp.probabilities_json,fp.expected_return_pct,ao.actual_direction,ao.actual_return_pct FROM final_predictions fp JOIN actual_outcomes ao ON ao.final_prediction_id=fp.id LEFT JOIN evaluation_results er ON er.final_prediction_id=fp.id WHERE er.id IS NULL")->fetchAll(PDO::FETCH_ASSOC);$m=new MetricsCalculator();$done=0;
foreach($rows as $r){$p=json_decode($r['probabilities_json'],true,flags:JSON_THROW_ON_ERROR);$probs=array_map('floatval',array_values($p));if(count($probs)!==3)continue;$confidence=max($probs);$bucket=$confidence>=.8?'HIGH':($confidence>=.6?'MEDIUM':'LOW');$hasReturn=$r['expected_return_pct']!==null && $r['actual_return_pct']!==null;$s=$pdo->prepare('INSERT OR IGNORE INTO evaluation_results(final_prediction_id,direction_correct,absolute_error_pct,squared_error,brier_score,calibration_bucket,evaluated_at) VALUES(?,?,?,?,?,?,?)');$s->execute([$r['id'],$m->directionCorrect($r['direction'],$r['actual_direction'])?1:0,$hasReturn?$m->absoluteError((float)$r['expected_return_pct'],(float)$r['actual_return_pct']):null,$hasReturn?$m->squaredError((float)$r['expected_return_pct'],(float)$r['actual_return_pct']):null,$m->brier($probs[0],$probs[1],$probs[2],$r['actual_direction']),$bucket,gmdate('c')]);$done++;}
echo json_encode(['evaluated'=>$done],JSON_THROW_ON_ERROR).PHP_EOL;
