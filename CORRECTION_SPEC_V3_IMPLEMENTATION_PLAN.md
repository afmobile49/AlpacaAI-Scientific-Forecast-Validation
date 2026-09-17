# CORRECTION_SPEC_V3 — برنامه پیاده‌سازی Stage 1

## A. درک من از مشکل فعلی

AlpacaAIApp اکنون این بخش‌ها را با موفقیت دارد:

- دریافت داده از Alpaca
- Quant V1
- Feature Set و Snapshot hash
- Outcome Rule با آستانه ±۰٫۵٪
- Evaluation و Benchmarkهای پایه
- FRF persistence اولیه
- گزارش HTML، پنل عمومی و ارسال ایمیل
- retry و pagination اولیه Alpaca
- Trading و Paper Trading غیرفعال

اما پروژه هنوز `QUANT_PROTOTYPE` است، چون:

- Targetها در runner اصلی به‌طور کامل ایجاد نمی‌شوند.
- همه horizonها وجود ندارند.
- Outcome/Evaluation/Benchmark/FRF هنوز شواهد واقعی production ندارند.
- rolling FRF عملیاتی نیست.
- `system_jobs` در lifecycle واقعی استفاده نمی‌شود.
- AI production wiring انجام نشده است.
- paired AI/Quant و PRE_AI exclusion وجود ندارد.
- expected return هنوز مقدار `0.0` دارد، درحالی‌که باید در نبود مدل علمی `NULL` باشد.

بنابراین پروژه هنوز `AI_QUANT_READY_FOR_VALIDATION` یا `FULL_FORWARD_VALIDATION_RUNNING` نیست.

## B. جریان فعلی production

### Currently wired

```text
run_forecast.php
→ AlpacaProvider
→ CompletedMarketBarGuard
→ FeatureSetV1
→ QuantForecastEngine
→ SnapshotPersistence
→ quant_predictions
→ final_predictions
```

```text
resolve_outcomes.php
→ matured targets
→ Alpaca latest price
→ actual_outcomes
```

```text
evaluate_outcomes.php
→ actual_outcomes
→ evaluation_results
```

```text
run_benchmarks.php
→ evaluation/outcomes
→ benchmark_results
```

```text
aggregate_frf.php
→ evaluation_results
→ reliability_runs / reliability_scores
```

```text
generate_daily_report.php
→ DailyReportDataBuilder
→ DailyReportViewModel
→ HTML / Email / Panel
```

### Exists but not fully wired

- `ForecastTargetFactory`
- `ForecastTargetRepository`
- `ReferencePrice`
- `SnapshotAgreement`
- `AiProductionGate`
- `ReportArchiveRepository`
- `RetryingEmailDeliveryService`

### Missing or incomplete

- اتصال Target creation به تمام Final Forecastها
- horizonهای کامل
- rolling FRF
- system job lifecycle
- AI production path
- paired AI/Quant persistence
- PRE_AI exclusion
- activation timestamp

## C. معماری هدف پس از اصلاح

```text
Market Data
↓
Completed-Bar / Cutoff Validation
↓
Feature Snapshot + Market Snapshot
↓
Quant V1
↓
AI_QUANT_V1 با همان snapshot
↓
Immutable Final Predictions
↓
Forecast Targets
↓
Reference Price
↓
Future Outcomes
↓
Evaluation
↓
Benchmarks
↓
FRF 2.0: cumulative و rolling
↓
DailyReportViewModel
↓
Dashboard / Email / Plain Text / Archive
```

AI فقط بعد از PASS شدن کامل Quant forward-validation فعال خواهد شد.

## D. فایل‌های مورد انتظار برای تغییر

### فایل‌های اصلاحی

- `cron/run_forecast.php` — اتصال Targetهای کامل، reference lifecycle، system jobs و cutoff واحد.
- `cron/resolve_outcomes.php` — پشتیبانی کامل horizonها، transaction و job state.
- `cron/evaluate_outcomes.php` — اتصال مستقیم و idempotent به outcomeهای resolved.
- `cron/run_benchmarks.php` — اضافه‌کردن `PREVIOUS_SESSION_DIRECTION` و benchmarkهای paired.
- `cron/aggregate_frf.php` — پشتیبانی از چهار window و محاسبه از raw eligible records.
- `app/Evaluation/ForecastTargetFactory.php` — horizonهای 1D/5D/20D سهام و 24H/7D/30D کریپتو.
- `app/Evaluation/ForecastTargetRepository.php` — duplicate protection و ثبت کامل lifecycle.
- `app/Forecast/ReferencePrice.php` — enforce کردن ترتیب freeze، finalization و target.
- `app/Reports/DailyReportDataBuilder.php` — خروجی کامل Quant، AI، FRF windows، trend و benchmark edge.
- `app/Reports/DailyReportViewModel.php` — مدل واحد مورد استفاده تمام rendererها.
- `app/Reports/DailyHtmlRenderer.php` — فقط rendering؛ بدون محاسبه مستقل metric.
- `app/Reports/DailyTextRenderer.php` — استفاده از همان ViewModel.
- `app/Reports/ReportArchive.php` — ثبت metadata کامل archive.
- `app/Reports/DeliveryRepository.php` — lifecycle کامل PENDING/SENT/FAILED/SKIPPED و retry.
- `app/MarketData/MarketCalendarService.php` — session arithmetic کامل برای همه horizonها.
- `app/MarketData/AlpacaProvider.php` — تکمیل Retry-After و تست خطاهای 429/5xx.
- `app/Jobs/JobLock.php` و `app/Jobs/JobState.php` — اتصال واقعی به تمام cronها.
- `app/AI/OpenAIProvider.php` — فقط پس از عبور gate، با retry روی همان snapshot.
- `app/AI/AiProductionGate.php` — enforce کردن گیت‌های علمی قبل از AI.
- `database/migrations/006_correction_v3.sql` — اصلاح schema برای expected return، horizons، pairing، activation و metadata.

### فایل‌های جدید

- `cron/create_targets.php` در صورت نیاز به جداسازی target lifecycle
- `cron/run_frf_windows.php`
- `app/Forecast/PairedForecastRepository.php`
- `app/Reliability/FrfWindowAggregator.php`
- `app/AI/AiQuantPairing.php`
- `app/Reports/ReportMetricSnapshot.php`

### تست‌های جدید

- `tests/ForecastTargetFactoryTest.php`
- `tests/TargetLifecycleIntegrationTest.php`
- `tests/ReferencePriceLifecycleTest.php`
- `tests/OutcomeEvaluationIntegrationTest.php`
- `tests/BenchmarkIntegrationTest.php`
- `tests/FrfRollingWindowTest.php`
- `tests/SystemJobLifecycleTest.php`
- `tests/QuantForwardOfflineE2ETest.php`
- `tests/AiQuantPairingTest.php`
- `tests/ReportConsistencyTest.php`
- `tests/AlpacaRetryPaginationTest.php`

## E. تغییرات دیتابیس

### جداول موجود

- `market_bars`
- `market_snapshots`
- `quant_predictions`
- `ai_predictions`
- `final_predictions`
- `forecast_targets`
- `actual_outcomes`
- `evaluation_results`
- `benchmark_results`
- `reliability_runs`
- `reliability_scores`
- `reliability_group_scores`
- `report_archives`
- `report_deliveries`
- `system_jobs`

### موارد مورد نیاز

- nullable شدن `expected_return_pct` در جداول مرتبط
- ثبت horizon و rule کامل Target
- pairing identifier برای Quant/AI
- activation timestamp برای experiment
- framework version در archive
- indexهای مربوط به target و rolling windows
- وضعیت کامل jobها

قبل از migration:

1. از SQLite production backup گرفته می‌شود.
2. hash و حجم backup ثبت می‌شود.
3. migration ابتدا روی clone محلی اجرا می‌شود.
4. تست integrity و row-count انجام می‌شود.
5. سپس migration روی production اجرا می‌شود.

هیچ رکورد فعلی حذف نخواهد شد.

## F. تحلیل ریسک و کنترل آن

- Forecastهای فعلی: حفظ می‌شوند؛ به‌عنوان PRE_AI باقی می‌مانند.
- `final_predictions`: immutable باقی می‌ماند.
- ایمیل: duplicate prevention حفظ می‌شود.
- پنل عمومی: رفتار فعلی حفظ می‌شود.
- duplicate targets: با UNIQUE و `INSERT OR IGNORE`.
- migration: backup و اجرای مرحله‌ای.
- AI API: فقط بعد از gate و با همان snapshot.
- اجرای دوباره Cron: با `system_jobs` و unique keys کنترل می‌شود.
- داده‌های PRE_AI: در paired analysis وارد نمی‌شوند.
- experiment contamination: activation timestamp و experiment boundary اعمال می‌شود.

## G. برنامه تست

### Unit

- horizon calculation
- calendar/DST/holiday
- outcome ±۰٫۵٪
- expected return NULL
- FRF formulas
- hash determinism
- system job states
- AI schema validation

### Integration

- Target creation
- Outcome resolution
- Evaluation
- Benchmark persistence
- FRF persistence
- archive/email lifecycle

### Offline End-to-End

```text
fake bars
→ cutoff guard
→ features
→ snapshot
→ Quant
→ freeze
→ targets
→ future fake bars
→ outcomes
→ evaluation
→ benchmarks
→ FRF windows
→ report ViewModel
→ HTML/plain text
```

هیچ Alpaca یا OpenAI واقعی در این تست استفاده نمی‌شود.

### Production-safe smoke test

- read-only Alpaca health
- runner path بدون duplicate
- target count
- job state
- report generation
- email duplicate prevention
- بدون ساخت outcome جعلی
- بدون فعال‌سازی Trading

## H. ترتیب اجرای پیشنهادی

1. Scientific integrity و cutoff
2. Target lifecycle و horizonها
3. Reference Price
4. Outcome و Evaluation
5. Benchmark
6. FRF persistence و rolling windows
7. system jobs
8. reporting consistency
9. Quant offline E2E
10. AI gate
11. AI production
12. paired AI/Quant
13. AI+Quant E2E
14. production smoke test
15. fresh verification audit

## I. مواردی که تغییر نمی‌دهم

- رفتار اصلی Alpaca integration مگر برای hardening ضروری
- منطق Quant V1 و thresholdهای آن
- XAUT fail-closed
- Trading و Paper Trading disabled
- ایمیل موفق و duplicate prevention
- SHA-256 و canonical hashing معتبر
- public report behavior معتبر
- رکوردهای تاریخی موجود

## J. ابهامات مهم

ابهام مهمی که از specification و کد قابل حل نباشد فعلاً وجود ندارد.

تنها تصمیم فنی لازم این است که در نبود مدل علمی expected return، مقدار آن `NULL` باشد؛ این مورد مستقیماً در `CORRECTION_SPEC_V3` تعیین شده است.

## K. رأی پیش از پیاده‌سازی

```text
PROPOSED_IMPLEMENTATION_STATUS:
READY_FOR_USER_REVIEW

CODE_CHANGED:
NO

DATABASE_CHANGED:
NO

PRODUCTION_CHANGED:
NO

WAITING_FOR_USER_APPROVAL:
YES
```

برای شروع Stage 2، تأیید صریح کاربر لازم است.
