ALTER TABLE forecast_targets ADD COLUMN reference_price REAL;
ALTER TABLE forecast_targets ADD COLUMN reference_price_time TEXT;
ALTER TABLE forecast_targets ADD COLUMN reference_rule TEXT NOT NULL DEFAULT 'REFERENCE_PRICE_RULE_V1';
ALTER TABLE forecast_targets ADD COLUMN reference_status TEXT NOT NULL DEFAULT 'REFERENCE_PENDING';
CREATE INDEX IF NOT EXISTS idx_forecast_targets_reference ON forecast_targets(reference_status,target_time);
