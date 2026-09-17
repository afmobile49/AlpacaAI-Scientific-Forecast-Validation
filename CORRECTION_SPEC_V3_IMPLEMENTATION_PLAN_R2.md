# CORRECTION_SPEC_V3 — Stage 1 Implementation Plan R2

## A. درک اصلاح‌شده از مشکل

هسته Quant، Alpaca، cutoff guard، snapshot hash، گزارش، ایمیل و اجزای اولیه Outcome/Evaluation/Benchmark/FRF وجود دارند؛ اما lifecycle علمی هنوز production-complete نیست.

مهم‌ترین نواقص فعلی:

- Target creation در primary finalization lifecycle به‌طور کامل enforce نشده است.
- Targetهای تاریخی نباید بدون اثبات integrity backfill شوند.
- Outcome باید از قیمت دقیق target-time/session استفاده کند، نه latest price زمان اجرای resolver.
- Reference Price و Outcome Price دو مفهوم مستقل‌اند.
- expected return در نبود مدل علمی باید `NULL` باشد، نه صفر؛ این اصلاح باید با SQLite table reconstruction امن انجام شود.
- horizonهای کامل، rolling FRF، system jobs، paired AI/Quant و PRE_AI exclusion کامل نیستند.
- AI production باید فقط با readiness gate واقعی فعال شود.

به همین دلیل طبقه‌بندی فعلی `QUANT_PROTOTYPE` باقی می‌ماند.

## B. جریان فعلی و نقص آن

```text
run_forecast.php
→ AlpacaProvider
→ CompletedMarketBarGuard
→ FeatureSetV1
→ QuantForecastEngine
→ SnapshotPersistence
→ final_predictions
```

اجزای زیر وجود دارند اما wiring کامل یا evidence production ندارند:

- Target creation
- Reference capture
- Outcome resolution
- Evaluation
- Benchmark
- FRF windows
- system job state
- AI production
- paired AI/Quant

Resolver فعلی نباید latest price را به‌عنوان outcome علمی استفاده کند. پس از اصلاح، قیمت باید از target timestamp/session تعیین‌شده خوانده شود؛ در نبود داده باید `WAITING_FOR_DATA` یا retryable بماند.

## C. معماری هدف

```text
Market Data
↓
Completed-Bar / Forecast-Cutoff Validation
↓
Exact Market Snapshot + Feature Snapshot
↓
Quant V1
↓
FINAL + Immutable Hash
↓
Target Creation در همان lifecycle
↓
REFERENCE_PENDING
↓
Reference Capture در زمان مقرر
↓
Target-Time Outcome Price
↓
Outcome / Evaluation / Benchmarks
↓
FRF: CUMULATIVE + 90D + 30D + 7D
↓
Shared DailyReportViewModel
↓
Dashboard / Email / Plain Text / Archive
```

AI فقط پس از PASS شدن تمام گیت‌های Quant وارد همین مسیر می‌شود:

```text
same snapshot + same cutoff
→ Quant
→ AI_QUANT_V1
→ strict validation
→ paired immutable predictions
```

## D. فایل‌های مورد انتظار

### فایل‌های اصلاحی

- `cron/run_forecast.php` — finalization، Target creation فوری، activation boundary و job state.
- `cron/resolve_outcomes.php` — target-time price، WAITING_FOR_DATA و retry؛ بدون latest-price substitution.
- `cron/capture_reference_prices.php` — capture جداگانه Reference Price پس از زمان مجاز.
- `cron/evaluate_outcomes.php` — Evaluation خودکار پس از outcome.
- `cron/run_benchmarks.php` — اضافه‌کردن `PREVIOUS_SESSION_DIRECTION` و pairing.
- `cron/aggregate_frf.php` — محاسبه raw از چهار window، بدون میانگین FRSهای قبلی.
- `app/Evaluation/ForecastTargetFactory.php` — horizonهای stock 1D/5D/20D و crypto 24H/7D/30D.
- `app/Evaluation/ForecastTargetRepository.php` — identity صریح، مقایسه conflict و خطای integrity.
- `app/Forecast/ReferencePrice.php` — lifecycle و rule version.
- `app/MarketData/MarketCalendarService.php` — session arithmetic برای تعطیلات، DST و early close.
- `app/MarketData/AlpacaProvider.php` — target-time bar lookup، Retry-After و failure handling.
- `app/Jobs/JobLock.php` و `app/Jobs/JobState.php` — stateهای PENDING/RUNNING/SUCCESS/FAILED/SKIPPED و stale recovery.
- `app/Reports/DailyReportDataBuilder.php` — یک منبع واحد برای تمام metricها و windowها.
- `app/Reports/DailyReportViewModel.php` — metric snapshot مشترک.
- `app/Reports/DailyHtmlRenderer.php` — rendering صرف، با NULL به‌شکل `—`.
- `app/Reports/DailyTextRenderer.php` — استفاده از ViewModel مشترک.
- `app/Reports/ReportArchive.php` و `ReportArchiveRepository.php` — metadata کامل.
- `app/Reports/DeliveryRepository.php` و `RetryingEmailDeliveryService.php` — lifecycle و retry.
- `app/AI/AiProductionGate.php` — بررسی readiness واقعی، نه صرفاً وجود کلاس.
- `app/AI/OpenAIProvider.php` — اجرای retry با همان snapshot frozen.
- `app/Forecast/SnapshotAgreement.php` — enforcement paired identifiers.

### فایل‌های جدید احتمالی

- `database/migrations/006_correction_v3.sql`
- `app/Reliability/FrfWindowAggregator.php`
- `app/Forecast/PairedForecastRepository.php`
- `app/Evaluation/TargetTimePriceResolver.php`
- `cron/run_frf_windows.php`

### تست‌های جدید

- target-time delayed resolver test
- no-backfill legacy test
- target identity conflict test
- full stock/crypto horizon test
- reference/outcome separation test
- SQLite nullable migration clone test
- exact market-bar auditability test
- rolling FRF test
- system job recovery test
- Quant offline E2E test
- AI gate readiness test
- paired AI/Quant E2E test
- report consistency test
- Alpaca Retry-After/pagination test

## E. Database و Migration Strategy

جداول موجود شامل `market_bars`، `market_snapshots`، `quant_predictions`، `ai_predictions`، `final_predictions`، `forecast_targets`، `actual_outcomes`، `evaluation_results`، `benchmark_results`، FRF tables، archive و system jobs هستند.

Migration جدید باید:

- `expected_return_pct` را در محل‌های لازم nullable کند.
- legacy methodology/version را بدون تغییر immutable FINAL حفظ کند.
- target identity را بر اساس `final_prediction_id + horizon_code + target_rule_version` enforce کند.
- paired identifiers و experiment activation timestamp را اضافه کند.
- archive framework version و status را ذخیره کند.
- indexهای rolling و target-time را ایجاد کند.

برای SQLite:

1. از production database backup گرفته می‌شود.
2. backup hash و اندازه‌اش ثبت می‌شود.
3. migration روی clone اجرا می‌شود.
4. table replacement با حفظ ID، foreign key، index و unique constraint انجام می‌شود.
5. `foreign_key_check`، row-count و hash integrity بررسی می‌شود.
6. فقط پس از موفقیت کامل، migration production اجرا می‌شود.

رکوردهای legacy در محل خود باقی می‌مانند؛ در صورت نیاز با marker `PRE_VALIDATION` یا `PRE_AI_VALIDATION` از official statistics خارج می‌شوند. هیچ FINAL immutable درجا اصلاح نمی‌شود.

## F. Price و Target Methodology

Reference Price:

```text
forecast → FINAL → hash locked → target created
→ REFERENCE_PENDING → reference capture → REFERENCE_CAPTURED
```

Outcome Price، قیمت مستقل target timestamp/session است.

```text
actual_return_pct = (outcome_price - reference_price) / reference_price * 100
```

versionها:

- `REFERENCE_PRICE_RULE_V1`
- `OUTCOME_PRICE_RULE_V1`
- `OUTCOME_DIRECTION_RULE_V1`

برای resolver با تأخیر، latest price هرگز جایگزین target-time price نمی‌شود. نبود target-time data باید retryable باشد.

## G. Activation و Legacy Policy

یک boundary رسمی برای `QUANT_VALIDATION_ACTIVATED_AT` ایجاد می‌شود. رکوردهای قبل از آن، حتی اگر در دیتابیس باقی بمانند، وارد official forward-validation statistics نمی‌شوند.

برای AI نیز `AI_QUANT_VALIDATION_ACTIVATED_AT` فقط بعد از عبور همه گیت‌های Quant ثبت می‌شود.

Targetهای قدیمی خودکار backfill نمی‌شوند مگر اینکه تمام integrity requirements قابل اثبات باشد.

## H. Test Plan

### Unit

- calendar، DST، holiday و early close
- target-time price selection
- delayed resolver
- horizon arithmetic
- target identity/conflict
- reference/outcome separation
- NULL expected return
- FRF formulas و missing-data semantics
- system job states
- AI strict validation و gate

### Integration

- finalization → targets
- reference capture
- target-time outcome
- outcome → evaluation
- evaluation → benchmark
- benchmark → FRF
- archive/email lifecycle
- SQLite migration clone

### Offline E2E

```text
fake bars
→ cutoff guard
→ exact snapshot
→ Quant
→ freeze/hash
→ targets
→ reference capture
→ future target-time bars
→ outcomes
→ evaluation
→ benchmarks
→ all FRF windows
→ ViewModel
→ HTML/plain text
```

### Production-safe smoke test

- health و Alpaca read-only
- بدون backfill legacy
- بدون outcome جعلی
- کنترل job locks
- target creation برای forecast جدید
- report/email duplicate prevention
- Trading و Paper Trading خاموش

## I. Production Risk Controls

- رکوردهای قدیمی حذف یا mutate نمی‌شوند.
- conflict target با integrity error متوقف می‌شود، نه `INSERT OR IGNORE` خاموش.
- system_jobs فقط observability/locking است و جایگزین UNIQUE constraint نیست.
- retry AI از همان input snapshot استفاده می‌کند.
- experiment contamination با activation boundary کنترل می‌شود.
- migration فقط بعد از clone، backup و foreign-key verification اجرا می‌شود.
- latest price هرگز outcome target-time نیست.

## J. ترتیب Implementation

1. cutoff و completed-bar integrity
2. exact market snapshot auditability
3. target identity و horizonها
4. primary finalization target lifecycle
5. reference capture
6. target-time outcome resolver
7. Evaluation
8. Benchmarks
9. FRF persistence و rolling windows
10. system jobs
11. shared reporting و archive/email
12. Quant offline E2E
13. AI readiness gate
14. AI production wiring
15. paired AI/Quant و PRE_AI exclusion
16. AI+Quant E2E
17. production-safe smoke test
18. fresh post-correction audit

## K. مواردی که حفظ می‌شوند

- رفتار معتبر Quant V1
- Alpaca read-only integration
- XAUT fail-closed
- Trading و Paper Trading disabled
- ایمیل موفق و duplicate prevention
- canonical SHA-256 معتبر
- گزارش عمومی معتبر
- رکوردهای تاریخی موجود

## L. ابهامات

ابهام blocking باقی نمانده است. روش target-time price باید در کد به‌صورت versioned و deterministic پیاده‌سازی شود؛ در نبود داده کافی، وضعیت retryable خواهد بود و latest price استفاده نمی‌شود.

## M. Pre-Implementation Verdict

```text
PLAN_VERSION:
CORRECTION_SPEC_V3_PLAN_R2

CODE_CHANGED:
NO

DATABASE_CHANGED:
NO

PRODUCTION_CHANGED:
NO

MANDATORY_CORRECTIONS_INCORPORATED:
YES

WAITING_FOR_USER_APPROVAL:
YES
```
