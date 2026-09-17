CREATE TABLE IF NOT EXISTS forecast_pairs (
 id INTEGER PRIMARY KEY AUTOINCREMENT,
 experiment_id INTEGER NOT NULL REFERENCES experiments(id),
 asset_id INTEGER NOT NULL REFERENCES assets(id),
 horizon_code TEXT NOT NULL,
 forecast_cycle_id TEXT NOT NULL,
 information_cutoff TEXT NOT NULL,
 market_snapshot_id INTEGER NOT NULL REFERENCES market_snapshots(id),
 feature_snapshot_hash TEXT NOT NULL,
 status TEXT NOT NULL,
 created_at TEXT NOT NULL,
 UNIQUE(experiment_id,asset_id,horizon_code,forecast_cycle_id)
);
ALTER TABLE final_predictions ADD COLUMN forecast_pair_id INTEGER NULL REFERENCES forecast_pairs(id);
CREATE INDEX IF NOT EXISTS idx_final_predictions_pair ON final_predictions(forecast_pair_id);
