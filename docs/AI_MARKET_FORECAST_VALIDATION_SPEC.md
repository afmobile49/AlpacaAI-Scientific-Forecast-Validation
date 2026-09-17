مشخصات اجرایی سیستم ارزیابی پیش‌بینی بازار با PHP برای Codex
AI Market Forecast Validation System — PHP 8.4 + SQLite + cPanel Cron
نسخه 1.0


نام پروژه: AI Market Forecast Validation System
محیط هدف: cPanel Shared Hosting
Backend: PHP 8.4
Database: SQLite
Scheduler: cPanel Cron
هدف: تولید خودکار پیش‌بینی بازار، قفل و ثبت غیرقابل‌ویرایش پیش‌بینی، دریافت نتیجه واقعی در آینده، و سنجش علمی عملکرد پیش‌بینی‌ها بدون انجام معامله واقعی.


1. هدف پروژه


این پروژه باید یک سیستم خودکار پژوهشی و قابل ممیزی ایجاد کند که برای مجموعه مشخصی از دارایی‌ها در زمان‌های از پیش تعیین‌شده پیش‌بینی تولید کند، تمام ورودی‌های مؤثر بر آن پیش‌بینی را با timestamp ثبت کند، پیش‌بینی را قفل کند، پس از رسیدن افق زمانی قیمت واقعی بازار را دریافت کند، و نتیجه را با معیارهای ریاضی و benchmarkهای ساده مقایسه کند.


نسخه 1 نباید معامله واقعی یا Paper Trading انجام دهد.


اصل حاکم:
Known Information
→ Timestamp + Information Cutoff
→ Market Snapshot
→ Quant Baseline
→ AI + Quant Forecast
→ Validation
→ Immutable Forecast
→ Wait for Horizon
→ Actual Market Outcome
→ Deterministic Evaluation
→ Benchmark Comparison
→ Dashboard / Reports


2. تصمیم‌های قطعی پروژه


Hosting: cPanel Shared Hosting
Scheduler: Cron
PHP: 8.4
Database: SQLite
Stocks: SPY, QQQ, AAPL, MSFT, NVDA
Crypto: BTC/USD, ETH/USD
Tokenized Gold: XAUT/USD
Stock Horizons: 1 trading day, 5 trading days, 20 trading days
Crypto/XAUT Horizons: 24 hours, 7 days, 30 days
Forecast Time: Pre-market for stocks
Stock Reference Price: Defined market-open price
Crypto/XAUT Reference Price: Price at forecast timestamp / closest validated bar
V1 Models: Quant Baseline vs AI + Quant
Trading: Disabled
Paper Trading: Disabled
Forecast Mutation: Forbidden after FINAL
Evaluation: Deterministic PHP
Primary Storage Timezone: UTC
UI Timezone: America/Los_Angeles
US Market Timezone: America/New_York
Minimum initial observation window: 90 days
Preferred evaluation window: 180 days


3. محدوده نسخه 1


داخل محدوده:
• دریافت داده بازار از Providerها.
• محاسبه اندیکاتورهای کمی در PHP.
• ساخت snapshot کامل از داده موجود تا Information Cutoff.
• تولید Quant Baseline.
• تولید AI + Quant Forecast از API یک LLM.
• اعتبارسنجی سخت‌گیرانه JSON خروجی AI.
• ثبت immutable پیش‌بینی.
• SHA-256 hash برای تشخیص تغییر.
• تعریف افق‌های ارزیابی.
• دریافت outcome واقعی.
• محاسبه معیارهای accuracy / error / calibration.
• ساخت benchmarkهای ساده.
• dashboard تحت وب.
• ثبت log، وضعیت job و خطاها.
• backup روزانه SQLite.
• health check.


خارج از محدوده نسخه 1:
• معامله واقعی.
• Paper Trading.
• Options.
• High-frequency trading.
• WebSocket دائمی.
• پیش‌بینی دقیقه‌ای.
• Portfolio optimization واقعی.
• بیش از 8 دارایی.
• تصمیم خودکار درباره position sizing.
• multi-agent کامل.
• خبر، fundamentals و macro در اولین نسخه پایدار.


4. معماری کلان


cPanel Hosting
  ↓ Cron
Daily Orchestrator
  ├─ Market Data Collectors
  ├─ Quant Engine
  └─ AI Engine (AI+Quant)
          ↓
        SQLite
          ↓
   Forecast Freeze
          ↓
 Future Outcome Jobs
          ↓
 Evaluation Engine
          ↓
 Benchmark / Metrics
          ↓
       Dashboard


قانون مهم: هیچ component نباید به آینده دسترسی داشته باشد. Forecast Engine فقط داده با timestamp کوچکتر یا مساوی Information Cutoff را می‌بیند.


5. ساختار پروژه


market-forecast-validator/
├── app/
│   ├── Config/
│   │   ├── AppConfig.php
│   │   ├── AssetConfig.php
│   │   └── ExperimentConfig.php
│   ├── Database/
│   │   ├── Connection.php
│   │   ├── Migrator.php
│   │   └── BackupService.php
│   ├── MarketData/
│   │   ├── MarketDataProviderInterface.php
│   │   ├── AlpacaProvider.php
│   │   ├── XautProvider.php
│   │   ├── MarketDataService.php
│   │   └── MarketCalendarService.php
│   ├── Indicators/
│   │   ├── IndicatorInterface.php
│   │   ├── SMA.php
│   │   ├── EMA.php
│   │   ├── RSI.php
│   │   ├── ATR.php
│   │   ├── Momentum.php
│   │   └── Volatility.php
│   ├── Forecast/
│   │   ├── QuantForecastEngine.php
│   │   ├── AiForecastEngine.php
│   │   ├── ForecastSchemaValidator.php
│   │   ├── ForecastFreezer.php
│   │   └── ForecastService.php
│   ├── AI/
│   │   ├── AiProviderInterface.php
│   │   └── OpenAIProvider.php
│   ├── Evaluation/
│   │   ├── OutcomeResolver.php
│   │   ├── EvaluationEngine.php
│   │   ├── MetricsCalculator.php
│   │   ├── CalibrationCalculator.php
│   │   └── BenchmarkEngine.php
│   ├── Jobs/
│   │   ├── JobLock.php
│   │   ├── JobState.php
│   │   └── JobRunner.php
│   ├── Repositories/
│   │   ├── AssetRepository.php
│   │   ├── MarketRepository.php
│   │   ├── ForecastRepository.php
│   │   ├── EvaluationRepository.php
│   │   └── JobRepository.php
│   └── Support/
│       ├── Logger.php
│       ├── Retry.php
│       ├── Clock.php
│       └── Json.php
├── cron/
│   ├── market_scheduler.php
│   ├── run_daily_pipeline.php
│   ├── resolve_outcomes.php
│   ├── calculate_metrics.php
│   ├── backup_database.php
│   └── health_check.php
├── config/
│   ├── .env.example
│   └── assets.php
├── database/
│   ├── forecast.sqlite
│   └── migrations/
│       └── 001_initial.sql
├── public/
│   ├── index.php
│   ├── prediction.php
│   ├── assets.php
│   ├── models.php
│   ├── health.php
│   └── assets/
├── storage/
│   ├── logs/
│   ├── backups/
│   └── exports/
├── tests/
│   ├── Unit/
│   ├── Integration/
│   └── Fixtures/
├── composer.json
├── README.md
└── CODEX_TASKS.md


فقط پوشه public/ باید از وب قابل دسترسی باشد. فایل SQLite، .env، logs و source code نباید داخل document root عمومی باشند.


6. تنظیمات محیطی


APP_ENV=production
APP_TIMEZONE=America/Los_Angeles
MARKET_TIMEZONE=America/New_York
STORAGE_TIMEZONE=UTC
DB_PATH=/absolute/path/to/market-forecast-validator/database/forecast.sqlite
ALPACA_API_KEY=
ALPACA_API_SECRET=
ALPACA_DATA_BASE_URL=https://data.alpaca.markets
XAUT_PROVIDER=auto
XAUT_API_BASE_URL=
XAUT_API_KEY=
AI_PROVIDER=openai
OPENAI_API_KEY=
OPENAI_MODEL=
FORECAST_WINDOW_MINUTES_BEFORE_OPEN=75
INFORMATION_CUTOFF_MINUTES_BEFORE_FORECAST=15
LOG_LEVEL=INFO
ENABLE_TRADING=false
ENABLE_PAPER_TRADING=false


قانون امنیتی: API keyها هرگز در Git، log، HTML، exception عمومی یا database ذخیره نشوند.


7. SQLite Configuration


تمام connectionها باید با PDO SQLite باشند.
در هر connection:
PRAGMA foreign_keys = ON;
PRAGMA journal_mode = WAL;
PRAGMA busy_timeout = 5000;
PRAGMA synchronous = NORMAL;


در shared hosting از concurrent writerهای زیاد جلوگیری شود. Orchestrator باید write-heavy jobها را serial اجرا کند.


8. Database Schema


فایل database/migrations/001_initial.sql باید حداقل جداول زیر را ایجاد کند:
assets
experiments
model_versions
prompt_versions
market_bars
market_snapshots
forecast_runs
quant_predictions
ai_predictions
final_predictions
forecast_targets
actual_outcomes
evaluation_results
benchmark_results
system_jobs
api_logs
system_events


الزامات schema:
• primary keyهای integer/autoincrement.
• foreign keyها فعال باشند.
• unique constraint برای جلوگیری از duplicate forecast/run/bar.
• index برای asset/time و pending targetها.
• direction فقط BULLISH/NEUTRAL/BEARISH.
• final_predictions پس از FINAL immutable باشد.
• همه timestampها ISO-8601 UTC ذخیره شوند.
• migrations idempotent و versioned باشند.


ساختارهای اصلی مورد نیاز:
assets: symbol, display_name, asset_type, provider, provider_symbol, exchange, timezone, is_24_7, enabled.
experiments: experiment_code, name, description, start_at, end_at, status, config_json.
model_versions: model_code, provider, model_name, parameters_json.
prompt_versions: prompt_code, sha256, prompt_text.
market_bars: asset_id, timeframe, bar_time, OHLCV, provider, raw_hash.
market_snapshots: experiment_id, asset_id, information_cutoff, snapshot_time, features_json, source_data_hash.
forecast_runs: experiment_id, run_key, scheduled_for, started_at, completed_at, status, state_detail.
quant_predictions: run_id, asset_id, horizon_code, direction, probabilities, expected_return_pct, confidence, features_json, algorithm_version.
ai_predictions: run_id, asset_id, horizon_code, direction, probabilities, expected_return_pct, confidence, reason_codes_json, explanation, model_version_id, prompt_version_id, raw_response_hash.
final_predictions: experiment_id, run_id, asset_id, source_type, source_prediction_id, horizon_code, information_cutoff, generated_at, reference_price, reference_price_time, direction, probabilities, expected_return_pct, confidence, status, forecast_hash.
forecast_targets: final_prediction_id, target_time, target_rule, status, resolved_at.
actual_outcomes: final_prediction_id, actual_price, actual_price_time, actual_return_pct, actual_direction, MFE, MAE, source_provider, source_hash.
evaluation_results: final_prediction_id, direction_correct, absolute_error_pct, squared_error, brier_score, calibration_bucket, evaluated_at.
benchmark_results: experiment_id, asset_id, forecast_date, horizon_code, benchmark_code, predicted_direction, actual_direction, correct, metadata_json.
system_jobs: job_key, job_name, status, started_at, finished_at, error_message, metadata_json.
api_logs: provider, endpoint_group, http_status, latency_ms, success, error_code, created_at.
system_events: severity, event_type, message, metadata_json, created_at.


9. Seed اولیه Assets


SPY       STOCK           provider=ALPACA
QQQ       STOCK           provider=ALPACA
AAPL      STOCK           provider=ALPACA
MSFT      STOCK           provider=ALPACA
NVDA      STOCK           provider=ALPACA
BTC/USD   CRYPTO          provider=ALPACA
ETH/USD   CRYPTO          provider=ALPACA
XAUT/USD  TOKENIZED_GOLD  provider=XAUT_PROVIDER


XAUT نباید به‌زور به Alpaca متصل شود. XautProvider باید adapter مستقل باشد. اگر provider قابل اعتماد برای XAUT در محیط عملیاتی تنظیم نشده باشد، XAUT با وضعیت data unavailable علامت بخورد و برای آن forecast ساخته نشود؛ سیستم نباید قیمت حدس بزند.


10. Market Data Provider Contract


interface MarketDataProviderInterface
{
    public function getHistoricalBars(string $symbol, string $timeframe, DateTimeImmutable $start, DateTimeImmutable $end): array;
    public function getLatestValidatedPrice(string $symbol, DateTimeImmutable $notAfter): array;
    public function healthCheck(): bool;
}


خروجی استاندارد bar شامل time, open, high, low, close, volume, provider باشد. هر provider باید داده خام را به format داخلی normalize کند.


11. Market Calendar


برای سهام نباید فرض شود هر روز کاری روز معامله است.
MarketCalendarService باید:
• تعطیلات بازار را تشخیص دهد.
• early close را تشخیص دهد.
• زمان بازشدن بازار را در America/New_York نگه دارد.
• targetهای 1D/5D/20D را براساس trading session محاسبه کند.
• زمان‌ها را در DB به UTC تبدیل کند.
اگر market calendar قابل بازیابی نیست، forecast سهام آن روز تولید نشود.


12. Scheduling


cPanel Cron ترجیحاً هر 15 دقیقه scheduler را بیدار کند:
*/15 * * * * /usr/local/bin/php /absolute/path/cron/market_scheduler.php >/dev/null 2>&1


کد scheduler خودش تعیین می‌کند آیا job باید اجرا شود یا خیر.


سهام:
forecast_window_start = market_open - 90 minutes
forecast_window_end   = market_open - 60 minutes
information_cutoff    = forecast_generation_time - 15 minutes
اگر job یک‌بار در window با موفقیت اجرا شد، تکرار نشود.


Crypto/XAUT:
در نسخه 1 یک run روزانه هم‌زمان با run سهام انجام شود. چون 24/7 هستند، targetهای آن‌ها براساس ساعت واقعی محاسبه شوند.


13. Job Lock و Idempotency


هر job یک job_key deterministic داشته باشد، مثال:
forecast:EXP-001:2026-09-10
resolve:2026-09-10T20
backup:2026-09-10


قبل از شروع:
1. BEGIN IMMEDIATE transaction.
2. تلاش برای INSERT در system_jobs.
3. اگر UNIQUE conflict رخ داد، job با SKIPPED خارج شود.
4. transaction کوتاه نگه داشته شود.
5. هیچ API call درون transaction طولانی انجام نشود.


هدف: اجرای تکراری Cron نباید رکورد تکراری ایجاد کند.


14. State Machine اجرای Forecast


CREATED
→ MARKET_DATA_READY
→ FEATURES_READY
→ QUANT_READY
→ AI_READY
→ VALIDATED
→ FINALIZED


اگر هر مرحله fail شد، current_state → FAILED و مراحل بعدی اجرا نشوند.
اصل: Unknown State = No Forecast.


15. Technical Feature Engine


محاسبات باید در PHP انجام شوند، نه در LLM.
حداقل featureها:
• SMA20
• SMA50
• EMA20
• EMA50
• RSI14
• ATR14
• Momentum 5
• Momentum 20
• rolling volatility 20
• current price distance from SMA20
• current price distance from SMA50
• recent volume ratio versus 20-period average


فرمول‌ها unit test داشته باشند. هیچ NaN/INF وارد forecast نشود. اگر history کافی نیست asset/horizon SKIPPED شود.


16. Quant Baseline V1


نسخه اول baseline شفاف و deterministic داشته باشد.
score = 0
EMA20 > EMA50 => +1
EMA20 < EMA50 => -1
Close > SMA20 => +1
Close < SMA20 => -1
RSI14 between 50-70 => +1
RSI14 between 30-50 => -1
RSI14 > 75 => -0.5
RSI14 < 25 => +0.5
Momentum5 > 0 => +1
Momentum5 < 0 => -1
Momentum20 > 0 => +1
Momentum20 < 0 => -1


Mapping اولیه:
score >= +2 => BULLISH
score <= -2 => BEARISH
otherwise => NEUTRAL


Confidence باید از magnitude score و consistency featureها محاسبه شود، نه عدد تصادفی. الگوریتم با نام QUANT_V1 versioned باشد و پارامترها در config قرار گیرند.


17. AI + Quant Contract


AI فقط featureهای از قبل محاسبه‌شده و context معتبر را دریافت کند.
Prompt باید بگوید:
• داده‌های بعد از Information Cutoff وجود ندارند.
• prediction فقط براساس payload داده‌شده باشد.
• هیچ داده بیرونی یا حدس unstated استفاده نشود.
• خروجی فقط JSON schema معتبر باشد.
• سه probability جمعاً تقریباً 1 باشند.
• confidence بین 0 و 1 باشد.
• reason_codes فقط از لیست مجاز باشند.
• explanation کوتاه و audit-friendly باشد.


JSON خروجی اجباری:
{
  "direction": "BULLISH",
  "probability_up": 0.71,
  "probability_neutral": 0.18,
  "probability_down": 0.11,
  "expected_return_pct": 2.8,
  "confidence": 0.74,
  "reason_codes": ["POSITIVE_TREND","POSITIVE_MOMENTUM","RSI_SUPPORTIVE"],
  "explanation": "Short audit explanation."
}


Reason codeهای V1:
POSITIVE_TREND
NEGATIVE_TREND
POSITIVE_MOMENTUM
NEGATIVE_MOMENTUM
RSI_SUPPORTIVE
RSI_OVERBOUGHT
RSI_OVERSOLD
HIGH_VOLATILITY
LOW_VOLATILITY
VOLUME_CONFIRMATION
MIXED_SIGNAL
INSUFFICIENT_CONVICTION


18. Forecast Schema Validation


قبل از ذخیره AI prediction:
• JSON parse شود.
• تمام fieldهای اجباری موجود باشند.
• direction فقط سه مقدار مجاز باشد.
• probabilityها بین 0 و 1 باشند.
• sum probabilityها در tolerance حدود 0.999 تا 1.001 باشد.
• confidence بین 0 و 1 باشد.
• expected_return_pct finite باشد.
• reason_codes از enum مجاز باشند.
• HTML/script در explanation ذخیره نشود.
• طول explanation محدود شود.


در صورت fail: AI_INVALID_RESPONSE → no AI final prediction.
سیستم حق ندارد JSON خراب را با حدس repair کند مگر sanitation مشخص و ثبت‌شده.


19. Forecast Immutability


بعد از final_predictions.status = FINAL:
• update محتوای prediction ممنوع.
• delete prediction ممنوع.
• correction باید رکورد جدید یا VOID audit event بسازد.
• forecast_hash از canonical payload ساخته شود.


Canonical payload شامل:
experiment_id, asset_id, source_type, horizon_code, information_cutoff, generated_at, reference_price, direction, probability_up, probability_neutral, probability_down, expected_return_pct, confidence.


Hash: SHA-256 از canonical JSON.
Health check باید نمونه‌ای از hashها را verify کند.


20. Reference Price Rules


Stocks:
Forecast قبل از بازار ساخته می‌شود. reference_price باید از open اولین regular-market bar در session همان روز گرفته شود. اگر open معتبر در window تعریف‌شده قابل دریافت نیست، prediction در حالت awaiting reference بماند. ارزیابی بدون reference price ممنوع است.


Crypto/XAUT:
reference price باید closest validated bar در یا بلافاصله پس از forecast time باشد، طبق rule ثابت و versioned.


21. Horizons


Stocks:
STOCK_1D = close اولین trading session بعد از reference session
STOCK_5D = close پنجمین trading session
STOCK_20D = close بیستمین trading session


Crypto/XAUT:
CRYPTO_24H = reference time + 24h
CRYPTO_7D = reference time + 7*24h
CRYPTO_30D = reference time + 30*24h


برای outcome از closest validated bar طبق tolerance ثابت استفاده شود.


22. تعریف جهت واقعی


V1:
actual_return > +0.50% => BULLISH
-0.50% <= return <= +0.50% => NEUTRAL
actual_return < -0.50% => BEARISH


threshold باید config/versioned باشد و در وسط EXP-001 تغییر نکند. در Experiment بعدی می‌توان volatility-adjusted threshold را آزمایش کرد.


23. Outcome Resolution


resolve_outcomes.php:
1. PENDING targetهایی را که target_time <= now دارند انتخاب کند.
2. data provider مناسب را تعیین کند.
3. actual target price را بگیرد.
4. actual return را محاسبه کند.
5. actual direction را طبق threshold تعیین کند.
6. در صورت امکان MFE و MAE را از barهای بین reference و target محاسبه کند.
7. actual_outcomes را ذخیره کند.
8. target را RESOLVED کند.
9. Evaluation Engine را اجرا کند.


اگر داده target هنوز unavailable است، target FAILED نشود؛ retryable state/event ثبت شود. FAILED فقط پس از policy مشخص.


24. Evaluation Metrics


برای هر prediction:
Direction Correct
Absolute Return Error
Squared Error
Brier Score
Calibration Bucket
MFE
MAE


Directional accuracy = correct predictions / resolved predictions
MAE = mean(abs(predicted_return - actual_return))
RMSE = sqrt(mean((predicted_return - actual_return)^2))
Brier برای probability جهت واقعی محاسبه شود.


Calibration buckets:
50-60
60-70
70-80
80-90
90-100


در dashboard accuracy واقعی هر bucket با confidence اعلام‌شده مقایسه شود.


25. Benchmarks V1


برای هر asset/horizon در همان Information Cutoff حداقل benchmarkهای زیر ساخته شوند:
ALWAYS_BULLISH
PREVIOUS_SESSION_DIRECTION
MOMENTUM_5
MOMENTUM_20
SMA20_VS_SMA50
QUANT_V1
AI_QUANT_V1


هدف پروژه این نیست که AI فقط بیشتر از 50% درست باشد. AI باید نسبت به baselineهای ساده edge نشان دهد.


26. Experiment Registry


EXP-001:
Code: EXP-001
Name: Quant vs AI+Quant Live Validation
Assets: 8
Start: deployment date
Minimum observation: 90 days
Preferred observation: 180 days
Trading: disabled
News: disabled
Fundamental: disabled
Macro: disabled


در طول EXP-001:
• Quant algorithm version تغییر نکند.
• Prompt version تغییر نکند.
• AI model تغییر نکند مگر failure اجباری؛ در آن صورت experiment split شود.
• threshold تغییر نکند.
• horizon definition تغییر نکند.
هر تغییر مهم = Experiment جدید.


27. Error Handling


No verified data → No forecast
No calendar → No stock forecast
Invalid feature set → No forecast
AI timeout → Quant may remain, AI omitted
Invalid AI JSON → No AI prediction
DB write failure → run FAILED
Unknown reference price → no evaluation
Unknown outcome → retry, not guess


Retry policy:
• 5xx: exponential backoff محدود.
• network timeout: retry محدود.
• 429: respect retry/backoff.
• 401/403: no blind retry; alert.
• schema/validation error: no retry مگر policy مشخص.


28. Logging


Structured logs ترجیحاً JSON Lines.
نمونه فیلدها: time, level, job, stage, asset, message, count.
هیچ secret در log ثبت نشود.
Retention پیشنهادی: 90 روز، سپس archive/delete policy.


29. Backup


هر شب backup_database.php اجرا شود.
Backup با SQLite-native safe method انجام شود، نه copy ساده هنگام write.
روش قابل قبول:
VACUUM INTO '/absolute/path/storage/backups/forecast-YYYY-MM-DD.sqlite';


Retention پیشنهادی:
• 14 daily backups
• 8 weekly backups
• 12 monthly backups


30. Security


• .env خارج public root.
• SQLite خارج public root.
• directory listing غیرفعال.
• Dashboard حداقل با authentication محافظت شود.
• output HTML با htmlspecialchars.
• SQL فقط prepared statements.
• query string مستقیماً concatenate نشود.
• CSRF برای action مدیریتی.
• dashboard در V1 read-only.
• API keys هرگز در browser ارسال نشوند.
• ENABLE_TRADING=false fail-closed باشد.
• هیچ endpoint سفارش‌گذاری در V1 نوشته نشود.


31. Dashboard


Overview:
Experiment
Experiment age
Total forecasts
Resolved forecasts
Pending forecasts
Quant accuracy
AI+Quant accuracy
AI edge over Quant
Best asset
Worst asset
Best horizon
Worst horizon
Calibration summary
System health
Last successful forecast run


Predictions filters:
date range, asset, horizon, source_type, direction, confidence bucket, correct/incorrect, experiment.


Prediction Detail:
generated_at, information_cutoff, snapshot hash, features, Quant prediction, AI prediction, final hash, reference price, target time, actual price, actual return, direction correctness, error metrics, reason codes, model version, prompt version.


Assets: performance per asset/horizon.
Models: performance per model/prompt/experiment.
Health: SQLite writable, last backup, Alpaca connectivity, XAUT provider connectivity, AI provider connectivity, failed jobs, unresolved stale targets, hash verification errors.


32. Tests


Unit Tests حداقل برای:
SMA, EMA, RSI, ATR, Momentum, Volatility, Quant score, direction threshold, horizon calculation, market-calendar session counting, Brier score, MAE/RMSE, canonical hash, AI JSON validation.


Integration Tests:
• SQLite migration on empty DB.
• duplicate Cron run does not duplicate records.
• Alpaca response normalization from fixture.
• XAUT response normalization from fixture.
• AI valid JSON accepted.
• AI invalid JSON rejected.
• missing market data blocks forecast.
• reference price assignment.
• outcome resolution.
• evaluation creation.
• backup creation.


API responses باید به‌صورت sanitized fixture ذخیره شوند تا test suite به live API وابسته نباشد.


33. Acceptance Criteria نسخه 1


1. composer install بدون خطا.
2. migration روی SQLite خالی بدون خطا.
3. seed 8 asset.
4. scheduler idempotent.
5. حداقل یک historical data import موفق با fixture.
6. feature engine unit tests pass.
7. Quant V1 prediction deterministic.
8. AI JSON schema validator tests pass.
9. final forecast immutable after FINAL.
10. duplicate execution cannot create duplicate forecast.
11. reference price rules testable.
12. outcome resolver با fixture کار کند.
13. evaluation metrics درست تولید شوند.
14. benchmarkها ثبت شوند.
15. dashboard Overview باز شود.
16. prediction detail قابل مشاهده باشد.
17. health page وضعیت componentها را نشان دهد.
18. SQLite backup ایجاد و verify شود.
19. هیچ secret در Git نباشد.
20. Trading code وجود نداشته باشد.
21. README شامل نصب cPanel و Cron باشد.
22. همه testها pass باشند.


34. مراحل اجرای Codex


Task 1 — Foundation
• repository structure
• composer.json
• dotenv/config loader
• PDO SQLite connection
• migration runner
• logger
• Clock abstraction
• PHPUnit setup
Gate: tests foundation pass.


Task 2 — Database
• migration 001
• seed assets
• repositories پایه
• WAL/busy_timeout
• integration test DB
Gate: clean DB can migrate and seed.


Task 3 — Market Data Abstraction
• interface
• Alpaca adapter
• XAUT adapter skeleton
• fixtures
• normalization tests
Gate: provider fixture tests pass.


Task 4 — Market Calendar
• trading day/session abstraction
• holiday/early close support
• target session counting
• tests
Gate: 1D/5D/20D session tests pass.


Task 5 — Indicators
• SMA/EMA/RSI/ATR/Momentum/Volatility
• deterministic fixtures
• tests
Gate: all numeric tests pass within tolerance.


Task 6 — Quant V1
• scoring engine
• confidence calculation
• versioning
• prediction repository
• tests
Gate: same input always same output.


Task 7 — AI Layer
• AiProviderInterface
• OpenAI provider
• request payload builder
• strict JSON validator
• prompt version storage
• mocked tests
Gate: invalid AI response cannot enter final table.


Task 8 — Forecast Orchestrator
• run state machine
• job locks
• information cutoff
• snapshots
• Quant + AI generation
• final freezing
• hash
• targets
Gate: duplicate run produces no duplicates.


Task 9 — Reference Prices + Outcomes
• stock open reference
• crypto/XAUT reference rule
• target resolver
• actual outcome
• retry behavior
Gate: fixture-based full forecast-to-outcome cycle passes.


Task 10 — Evaluation + Benchmarks
• accuracy
• MAE/RMSE
• Brier
• calibration bucket
• baselines
• aggregate queries
Gate: expected test calculations exactly match fixtures.


Task 11 — Dashboard
• read-only pages
• filters
• safe HTML
• responsive basic layout
• no secrets
Gate: manual smoke test + HTTP 200 pages.


Task 12 — Cron + Health + Backup
• scheduler
• resolver
• metrics
• backup
• health
• cPanel docs
Gate: CLI execution works with exit codes.


Task 13 — Security + Final QA
• secret scan
• SQL injection review
• XSS review
• path exposure review
• disable trading hard gate
• full tests
• README
Gate: release candidate tagged v1.0.0.


35. Exit Codes برای Cron


0 = SUCCESS
10 = SKIPPED / NOT DUE
20 = RETRYABLE EXTERNAL ERROR
30 = CONFIGURATION ERROR
40 = DATA VALIDATION ERROR
50 = DATABASE ERROR
60 = INTERNAL ERROR


CLI script باید top-level exception را catch و log کند و exit code مناسب بدهد.


36. Composer Dependencies پیشنهادی


php: ^8.4
guzzlehttp/guzzle: ^7.0
vlucas/phpdotenv: ^5.6
monolog/monolog: ^3.0
phpunit/phpunit: ^11.0 (dev)


اگر cPanel Composer محدودیت داشت، HTTP client می‌تواند به cURL native fallback داشته باشد.


37. Coding Standards


• declare(strict_types=1);
• PSR-4 autoloading.
• PSR-12 style.
• DateTimeImmutable.
• UTC in persistence.
• dependency injection ساده.
• repository pattern فقط در جایی که ارزش دارد.
• no global mutable state.
• no static API clients.
• no secrets hard-coded.
• no silent catch.
• no suppressed errors with @.
• no direct SQL in view files.
• business logic خارج public/.
• financial calculations با تست.


38. Definition of Done برای هر Feature


هر feature فقط وقتی Done است که:
• code نوشته شده.
• unit/integration test دارد.
• error paths بررسی شده.
• log مناسب دارد.
• README/config به‌روزرسانی شده اگر لازم است.
• هیچ secret اضافه نشده.
• lint/tests pass.
• behavior idempotent است اگر job محسوب می‌شود.


39. آینده پروژه بعد از EXP-001


EXP-002: Quant vs AI+Quant vs AI+Quant+News.
EXP-003: افزودن Macro.
EXP-004: افزودن Fundamental.
EXP-005: Multi-Agent.
EXP-006: Shadow Portfolio.
EXP-007: Paper Trading.


هیچ مرحله‌ای نباید صرفاً به دلیل جذاب بودن معماری اضافه شود. هر مرحله باید با evidence نسبت به baseline ارزش افزوده نشان دهد.


40. دستور مستقیم برای Codex


You are implementing a production-minded research system named “AI Market Forecast Validation System”.
Your source of truth is this specification document.


Environment:
- PHP 8.4
- SQLite
- cPanel shared hosting
- cPanel Cron
- Composer
- no long-running daemons
- no WebSocket requirement in V1
- all persistent timestamps UTC
- user-facing timezone America/Los_Angeles
- US stock market timezone America/New_York


Core purpose:
Generate live forward-looking market forecasts, freeze them immutably, wait until their horizons mature, retrieve actual market outcomes, and evaluate forecast quality against deterministic benchmarks.


Assets:
SPY, QQQ, AAPL, MSFT, NVDA, BTC/USD, ETH/USD, XAUT/USD.


Important:
XAUT must use a provider abstraction. Do not assume Alpaca supports XAUT. If a configured, verifiable XAUT provider is unavailable, skip XAUT safely. Never fabricate market data.


V1 forecasting:
- Quant V1 deterministic baseline.
- AI + Quant forecast.
- No trading.
- No paper trading.
- No order placement code.
- No portfolio execution logic.


Stock horizons:
1 trading day, 5 trading days, 20 trading days.


Crypto/XAUT horizons:
24 hours, 7 days, 30 days.


Scientific integrity requirements:
- Every forecast has an information cutoff.
- Only data at or before the cutoff may affect a forecast.
- Save a market/features snapshot and source hash.
- Once a final forecast is FINAL, its prediction content cannot be edited.
- Generate a SHA-256 canonical forecast hash.
- Future results are resolved by deterministic PHP code, not by an LM.
- Never infer or guess missing market data.
- Unknown state means no forecast.
- Model, prompt, threshold, and algorithm versions must be traceable.
- A material methodology change starts a new experiment.


Database:
Implement the SQLite schema in this specification and enable:
PRAGMA foreign_keys=ON
PRAGMA journal_mode=WAL
PRAGMA busy_timeout=5000
PRAGMA synchronous=NORMAL


Architecture:
Use PSR-4, strict_types, DateTimeImmutable, prepared SQL, service/repository separation, provider interfaces, deterministic job keys, job locks, idempotent cron behavior, structured logs, and explicit exit codes.


Market data:
Build MarketDataProviderInterface. Implement AlpacaProvider for supported stock/crypto symbols. Implement XautProvider as a separate adapter. Use sanitized fixtures for automated tests. Do not require live APIs to run the test suite.


AI:
Build AiProviderInterface and an OpenAIProvider. Do not let the LLM calculate technical indicators. PHP calculates all indicators. AI receives only validated features/context. Require strict structured JSON. Reject invalid responses rather than guessing corrections.


Indicators:
SMA20, SMA50, EMA20, EMA50, RSI14, ATR14, Momentum5, Momentum20, rolling volatility20, distance from SMA20/SMA50, volume ratio20.


Quant V1:
Implement the transparent scoring algorithm defined in this specification. Version the algorithm as QUANT_V1. Its output must be deterministic and testable.


Scheduling:
cPanel Cron wakes the scheduler every 15 minutes. The PHP scheduler decides whether a forecast is due. Stock forecast target is approximately 75 minutes before official market open. Do not hard-code DST assumptions. Use market calendar/session logic. Crypto/XAUT run daily in V1.


Evaluation:
Resolve actual outcomes when target horizons mature. Compute direction correctness, absolute error, squared error, MAE/RMSE aggregates, Brier score, calibration buckets, MFE and MAE when data is available. Run deterministic benchmarks: ALWAYS_BULLISH, PREVIOUS_SESSION_DIRECTION, MOMENTUM_5, MOMENTUM_20, SMA20_VS_SMA50, QUANT_V1, AI_QUANT_V1.


Dashboard:
Read-only V1. Pages: Overview, Predictions, Prediction Detail, Assets, Models, Health. Escape all output and use prepared statements.


Security:
.env and SQLite outside public root. No API keys in source/logs/browser. No trading endpoints. ENABLE_TRADING=false must remain fail-closed. No raw exception details to the public UI.


Implementation workflow:
Do not implement the entire project in one uncontrolled pass. Follow Tasks 1 through 13 from this specification. At the end of each task: run relevant tests, fix failures, report files changed, report tests run, and note unresolved issues. Proceed only after the current task is internally consistent.


Do not ask questions for matters already specified here. If a genuinely external value is unavailable (API key, exact XAUT provider, host absolute path), create a clearly named configuration placeholder and continue implementing everything else. Never invent credentials, prices, API responses, or broker capabilities.


Final delivery requirements:
- complete source tree
- migrations
- seed
- .env.example
- automated tests
- fixture data
- cPanel Cron examples
- installation guide
- operational guide
- backup/restore guide
- security notes
- experiment methodology notes
- all tests passing
- no trading capability
- release-ready V1 structure


41. راهنمای استفاده از این سند در Codex


بهترین روش:
1. این سند را داخل repository در مسیر docs/AI_MARKET_FORECAST_VALIDATION_SPEC.md قرار دهید.
2. فایل AGENTS.md در root بسازید و بنویسید سند فوق source of truth است.
3. Master Implementation Instruction بخش 40 را به Codex بدهید.
4. از Codex بخواهید Task 1 را انجام دهد.
5. بعد از Task 1 tests و diff را بررسی کنید.
6. سپس Task 2 تا Task 13 را ادامه دهید.
7. هیچ credential واقعی داخل repository قرار ندهید.
8. ابتدا fixture-based tests، سپس sandbox/live data connectivity.
9. deployment به cPanel فقط پس از pass کامل test suite.


نمونه AGENTS.md:
# Project Instructions
The authoritative implementation specification is:
docs/AI_MARKET_FORECAST_VALIDATION_SPEC.md
Follow it exactly.
Implementation must proceed in the task order defined in that specification.
Do not add trading or order-placement functionality.
Never fabricate missing external data or credentials.
All tests must use fixtures by default.
Production API access must be configuration-driven.


42. معیار ارزیابی معماری


این طرح از نظر مهندسی برای هدف «سنجش علمی پیش‌بینی AI در بازار» امتیاز 9.9/10 دارد، چون:
• داده آینده را از فرآیند forecast جدا می‌کند.
• prediction را immutable می‌کند.
• benchmark دارد.
• Quant و AI را جدا قابل مقایسه می‌کند.
• نتیجه را با LLM داوری نمی‌کند.
• تغییر methodology را versioned می‌کند.
• shared hosting و Cron را در نظر گرفته است.
• وابستگی XAUT را modular کرده است.
• failureها fail-closed هستند.
• مسیر رشد به News/Macro/Multi-Agent/Paper Trading بدون بازنویسی core فراهم است.


این امتیاز مربوط به کیفیت معماری و آزمایش است، نه تضمین accuracy یا سودآوری بازار.
