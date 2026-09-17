PRAGMA foreign_keys=OFF;
CREATE TABLE IF NOT EXISTS evaluation_results_v9 (
 id INTEGER PRIMARY KEY AUTOINCREMENT,
 final_prediction_id INTEGER NOT NULL UNIQUE REFERENCES final_predictions(id),
 direction_correct INTEGER NOT NULL,
 absolute_error_pct REAL NULL,
 squared_error REAL NULL,
 brier_score REAL NOT NULL,
 calibration_bucket TEXT NOT NULL,
 evaluated_at TEXT NOT NULL
);
INSERT OR IGNORE INTO evaluation_results_v9 SELECT id,final_prediction_id,direction_correct,absolute_error_pct,squared_error,brier_score,calibration_bucket,evaluated_at FROM evaluation_results;
DROP TABLE evaluation_results;
ALTER TABLE evaluation_results_v9 RENAME TO evaluation_results;
CREATE INDEX IF NOT EXISTS idx_eval_prediction ON evaluation_results(final_prediction_id);
PRAGMA foreign_keys=ON;
