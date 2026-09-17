CREATE TABLE IF NOT EXISTS assets (
  id INTEGER PRIMARY KEY AUTOINCREMENT, symbol TEXT NOT NULL UNIQUE, display_name TEXT NOT NULL,
  asset_type TEXT NOT NULL CHECK (asset_type IN ('STOCK','CRYPTO','TOKENIZED_GOLD')),
  provider TEXT NOT NULL, provider_symbol TEXT NOT NULL, exchange TEXT, timezone TEXT NOT NULL,
  is_24_7 INTEGER NOT NULL DEFAULT 0 CHECK (is_24_7 IN (0,1)), enabled INTEGER NOT NULL DEFAULT 1 CHECK (enabled IN (0,1))
);
CREATE TABLE IF NOT EXISTS experiments (
  id INTEGER PRIMARY KEY AUTOINCREMENT, experiment_code TEXT NOT NULL UNIQUE, name TEXT NOT NULL,
  description TEXT NOT NULL, start_at TEXT NOT NULL, end_at TEXT, status TEXT NOT NULL, config_json TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS model_versions (id INTEGER PRIMARY KEY AUTOINCREMENT, model_code TEXT NOT NULL UNIQUE, provider TEXT NOT NULL, model_name TEXT NOT NULL, parameters_json TEXT NOT NULL);
CREATE TABLE IF NOT EXISTS prompt_versions (id INTEGER PRIMARY KEY AUTOINCREMENT, prompt_code TEXT NOT NULL UNIQUE, sha256 TEXT NOT NULL, prompt_text TEXT NOT NULL);
CREATE TABLE IF NOT EXISTS market_bars (
  id INTEGER PRIMARY KEY AUTOINCREMENT, asset_id INTEGER NOT NULL REFERENCES assets(id), timeframe TEXT NOT NULL, bar_time TEXT NOT NULL,
  open REAL NOT NULL, high REAL NOT NULL, low REAL NOT NULL, close REAL NOT NULL, volume REAL NOT NULL, provider TEXT NOT NULL, raw_hash TEXT NOT NULL,
  UNIQUE(asset_id,timeframe,bar_time,provider)
);
CREATE TABLE IF NOT EXISTS market_snapshots (
  id INTEGER PRIMARY KEY AUTOINCREMENT, experiment_id INTEGER NOT NULL REFERENCES experiments(id), asset_id INTEGER NOT NULL REFERENCES assets(id),
  information_cutoff TEXT NOT NULL, snapshot_time TEXT NOT NULL, features_json TEXT NOT NULL, source_data_hash TEXT NOT NULL,
  UNIQUE(experiment_id,asset_id,information_cutoff)
);
CREATE TABLE IF NOT EXISTS forecast_runs (
  id INTEGER PRIMARY KEY AUTOINCREMENT, experiment_id INTEGER NOT NULL REFERENCES experiments(id), run_key TEXT NOT NULL UNIQUE,
  scheduled_for TEXT NOT NULL, started_at TEXT, completed_at TEXT, status TEXT NOT NULL, state_detail TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS quant_predictions (
  id INTEGER PRIMARY KEY AUTOINCREMENT, run_id INTEGER NOT NULL REFERENCES forecast_runs(id), asset_id INTEGER NOT NULL REFERENCES assets(id), horizon_code TEXT NOT NULL,
  direction TEXT NOT NULL CHECK(direction IN ('BULLISH','NEUTRAL','BEARISH')), probabilities_json TEXT NOT NULL, expected_return_pct REAL NULL, confidence REAL NOT NULL CHECK(confidence BETWEEN 0 AND 1), features_json TEXT NOT NULL, algorithm_version TEXT NOT NULL,
  UNIQUE(run_id,asset_id,horizon_code)
);
CREATE TABLE IF NOT EXISTS ai_predictions (
  id INTEGER PRIMARY KEY AUTOINCREMENT, run_id INTEGER NOT NULL REFERENCES forecast_runs(id), asset_id INTEGER NOT NULL REFERENCES assets(id), horizon_code TEXT NOT NULL,
  direction TEXT NOT NULL CHECK(direction IN ('BULLISH','NEUTRAL','BEARISH')), probabilities_json TEXT NOT NULL, expected_return_pct REAL NULL, confidence REAL NOT NULL CHECK(confidence BETWEEN 0 AND 1), reason_codes_json TEXT NOT NULL, explanation TEXT NOT NULL, model_version_id INTEGER REFERENCES model_versions(id), prompt_version_id INTEGER REFERENCES prompt_versions(id), raw_response_hash TEXT NOT NULL,
  UNIQUE(run_id,asset_id,horizon_code)
);
CREATE TABLE IF NOT EXISTS final_predictions (
  id INTEGER PRIMARY KEY AUTOINCREMENT, experiment_id INTEGER NOT NULL REFERENCES experiments(id), run_id INTEGER NOT NULL REFERENCES forecast_runs(id), asset_id INTEGER NOT NULL REFERENCES assets(id), source_type TEXT NOT NULL, source_prediction_id INTEGER NOT NULL, horizon_code TEXT NOT NULL, information_cutoff TEXT NOT NULL, generated_at TEXT NOT NULL, reference_price REAL NOT NULL, reference_price_time TEXT NOT NULL, direction TEXT NOT NULL CHECK(direction IN ('BULLISH','NEUTRAL','BEARISH')), probabilities_json TEXT NOT NULL, expected_return_pct REAL NULL, confidence REAL NOT NULL CHECK(confidence BETWEEN 0 AND 1), status TEXT NOT NULL, forecast_hash TEXT NOT NULL UNIQUE
);
CREATE TABLE IF NOT EXISTS forecast_targets (id INTEGER PRIMARY KEY AUTOINCREMENT, final_prediction_id INTEGER NOT NULL UNIQUE REFERENCES final_predictions(id), target_time TEXT NOT NULL, target_rule TEXT NOT NULL, status TEXT NOT NULL, resolved_at TEXT);
CREATE TABLE IF NOT EXISTS actual_outcomes (id INTEGER PRIMARY KEY AUTOINCREMENT, final_prediction_id INTEGER NOT NULL UNIQUE REFERENCES final_predictions(id), actual_price REAL NOT NULL, actual_price_time TEXT NOT NULL, actual_return_pct REAL NOT NULL, actual_direction TEXT NOT NULL, mfe REAL, mae REAL, source_provider TEXT NOT NULL, source_hash TEXT NOT NULL);
CREATE TABLE IF NOT EXISTS evaluation_results (id INTEGER PRIMARY KEY AUTOINCREMENT, final_prediction_id INTEGER NOT NULL UNIQUE REFERENCES final_predictions(id), direction_correct INTEGER NOT NULL, absolute_error_pct REAL NOT NULL, squared_error REAL NOT NULL, brier_score REAL NOT NULL, calibration_bucket TEXT NOT NULL, evaluated_at TEXT NOT NULL);
CREATE TABLE IF NOT EXISTS benchmark_results (id INTEGER PRIMARY KEY AUTOINCREMENT, experiment_id INTEGER NOT NULL REFERENCES experiments(id), asset_id INTEGER NOT NULL REFERENCES assets(id), forecast_date TEXT NOT NULL, horizon_code TEXT NOT NULL, benchmark_code TEXT NOT NULL, predicted_direction TEXT NOT NULL, actual_direction TEXT NOT NULL, correct INTEGER NOT NULL, metadata_json TEXT NOT NULL, UNIQUE(experiment_id,asset_id,forecast_date,horizon_code,benchmark_code));
CREATE TABLE IF NOT EXISTS system_jobs (id INTEGER PRIMARY KEY AUTOINCREMENT, job_key TEXT NOT NULL UNIQUE, job_name TEXT NOT NULL, status TEXT NOT NULL, started_at TEXT, finished_at TEXT, error_message TEXT, metadata_json TEXT NOT NULL);
CREATE TABLE IF NOT EXISTS api_logs (id INTEGER PRIMARY KEY AUTOINCREMENT, provider TEXT NOT NULL, endpoint_group TEXT NOT NULL, http_status INTEGER, latency_ms INTEGER, success INTEGER NOT NULL, error_code TEXT, created_at TEXT NOT NULL);
CREATE TABLE IF NOT EXISTS system_events (id INTEGER PRIMARY KEY AUTOINCREMENT, severity TEXT NOT NULL, event_type TEXT NOT NULL, message TEXT NOT NULL, metadata_json TEXT NOT NULL, created_at TEXT NOT NULL);
CREATE INDEX IF NOT EXISTS idx_market_bars_asset_time ON market_bars(asset_id,bar_time);
CREATE INDEX IF NOT EXISTS idx_forecast_targets_pending ON forecast_targets(status,target_time);
CREATE INDEX IF NOT EXISTS idx_final_predictions_asset_time ON final_predictions(asset_id,generated_at);
