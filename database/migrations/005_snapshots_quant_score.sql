ALTER TABLE market_snapshots ADD COLUMN feature_set_version TEXT NOT NULL DEFAULT 'FEATURE_SET_V1';
ALTER TABLE market_snapshots ADD COLUMN feature_snapshot_hash TEXT;
ALTER TABLE market_snapshots ADD COLUMN market_data_fingerprint TEXT;
ALTER TABLE market_snapshots ADD COLUMN market_bar_count INTEGER NOT NULL DEFAULT 0;
CREATE TABLE IF NOT EXISTS quant_scores (id INTEGER PRIMARY KEY AUTOINCREMENT, quant_prediction_id INTEGER NOT NULL UNIQUE, quant_score REAL NOT NULL, algorithm_version TEXT NOT NULL, created_at TEXT NOT NULL, FOREIGN KEY(quant_prediction_id) REFERENCES quant_predictions(id));
