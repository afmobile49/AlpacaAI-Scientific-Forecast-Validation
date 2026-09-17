PRAGMA foreign_keys=OFF;
CREATE TABLE IF NOT EXISTS forecast_targets_v3 (id INTEGER PRIMARY KEY AUTOINCREMENT,final_prediction_id INTEGER NOT NULL REFERENCES final_predictions(id),horizon_code TEXT NOT NULL,target_time TEXT NOT NULL,target_rule TEXT NOT NULL,status TEXT NOT NULL,resolved_at TEXT,UNIQUE(final_prediction_id,horizon_code,target_rule));
INSERT OR IGNORE INTO forecast_targets_v3(id,final_prediction_id,horizon_code,target_time,target_rule,status,resolved_at) SELECT id,final_prediction_id,'1D',target_time,target_rule,status,resolved_at FROM forecast_targets;
DROP TABLE forecast_targets;
ALTER TABLE forecast_targets_v3 RENAME TO forecast_targets;
CREATE INDEX IF NOT EXISTS idx_forecast_targets_pending ON forecast_targets(status,target_time);
PRAGMA foreign_keys=ON;
