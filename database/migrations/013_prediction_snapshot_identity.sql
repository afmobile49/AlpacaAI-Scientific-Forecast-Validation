ALTER TABLE quant_predictions ADD COLUMN information_cutoff TEXT NULL;
ALTER TABLE quant_predictions ADD COLUMN market_snapshot_id INTEGER NULL REFERENCES market_snapshots(id);
ALTER TABLE quant_predictions ADD COLUMN feature_snapshot_hash TEXT NULL;
ALTER TABLE ai_predictions ADD COLUMN information_cutoff TEXT NULL;
ALTER TABLE ai_predictions ADD COLUMN market_snapshot_id INTEGER NULL REFERENCES market_snapshots(id);
ALTER TABLE ai_predictions ADD COLUMN feature_snapshot_hash TEXT NULL;
CREATE INDEX IF NOT EXISTS idx_quant_prediction_snapshot ON quant_predictions(market_snapshot_id,information_cutoff);
CREATE INDEX IF NOT EXISTS idx_ai_prediction_snapshot ON ai_predictions(market_snapshot_id,information_cutoff);
