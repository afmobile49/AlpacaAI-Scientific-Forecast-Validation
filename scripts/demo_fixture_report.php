<?php
declare(strict_types=1);
require dirname(__DIR__) . '/vendor/autoload.php';
use MarketForecast\Database\{Connection, Migrator, Seeder};
use MarketForecast\Reports\{DailyReportDataBuilder, DailyReportViewModel, DailyHtmlRenderer};

$path = sys_get_temp_dir() . '/alpaca-demo-' . bin2hex(random_bytes(4)) . '.sqlite';
$pdo = Connection::open($path);
(new Migrator($pdo))->apply(dirname(__DIR__) . '/database/migrations', '2026-09-11T00:00:00Z');
(new Seeder($pdo))->seedAssets();
$pdo->exec("INSERT INTO experiments(experiment_code,name,description,start_at,status,config_json) VALUES('DEMO','Demo','fixture','2026-01-01','ACTIVE','{}')");
$experimentId = (int) $pdo->lastInsertId();
$pdo->exec("INSERT INTO forecast_runs(experiment_id,run_key,scheduled_for,started_at,status,state_detail,information_cutoff) VALUES($experimentId,'demo-run','2026-09-11','2026-09-11','FINALIZED','FINALIZED','2026-09-11T00:00:00Z')");
$runId = (int) $pdo->lastInsertId();
$assetId = (int) $pdo->query('SELECT id FROM assets ORDER BY id LIMIT 1')->fetchColumn();
$prediction = $pdo->prepare("INSERT INTO final_predictions(experiment_id,run_id,asset_id,source_type,source_prediction_id,horizon_code,information_cutoff,generated_at,reference_price,reference_price_time,direction,probabilities_json,expected_return_pct,confidence,status,forecast_hash) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
$prediction->execute([$experimentId, $runId, $assetId, 'QUANT', 1, '1D', '2026-09-11T00:00:00Z', '2026-09-11T00:00:00Z', 100, '2026-09-11T00:00:00Z', 'BULLISH', '[0.7,0.2,0.1]', null, .7, 'FINAL', 'demo-hash']);
$finalId = (int) $pdo->lastInsertId();
$pdo->exec("INSERT INTO forecast_targets(final_prediction_id,horizon_code,target_time,target_rule,status,reference_rule,reference_status,reference_price,reference_price_time) VALUES($finalId,'1D','2026-09-12T00:00:00Z','NEXT_24H_CLOSE','RESOLVED','REFERENCE_PRICE_RULE_V2','REFERENCE_CAPTURED',100,'2026-09-11T00:00:00Z')");
$pdo->exec("INSERT INTO actual_outcomes(final_prediction_id,actual_price,actual_price_time,actual_return_pct,actual_direction,source_provider,source_hash) VALUES($finalId,101,'2026-09-12T00:00:00Z',1,'BULLISH','DEMO','demo')");
$pdo->exec("INSERT INTO evaluation_results(final_prediction_id,direction_correct,absolute_error_pct,squared_error,brier_score,calibration_bucket,evaluated_at) VALUES($finalId,1,1,1,.14,'MEDIUM','2026-09-12')");
$data = (new DailyReportDataBuilder($pdo))->build('2026-09-11');
$html = (new DailyHtmlRenderer())->render((new DailyReportViewModel())->build($data));
$output = dirname(__DIR__) . '/email-preview-demo.html';
file_put_contents($output, $html);
echo json_encode(['database' => $path, 'html' => $output, 'summary' => $data['summary']], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
