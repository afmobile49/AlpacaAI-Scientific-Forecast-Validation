# AlpacaAIApp — FINAL_SCIENTIFIC_ACTIVATION_PLAN_V4_R2

## A1. درک وضعیت فعلی

هسته Quant forward-validation شامل featureها، snapshot hash، targetهای چندافقی، historical outcome resolver، evaluation، benchmark، FRF rolling windows و system jobs است. Quant offline E2E موفق است و Trading/Paper Trading خاموش هستند.

از نظر علمی، چهار خلأ اصلی باقی است: Reference Price سهام هنوز باید از OPEN جلسه بعدی بازار گرفته شود؛ cutoff واحد در تمام زنجیره وجود ندارد؛ gate فعلی readiness علمی را کامل بررسی نمی‌کند؛ و AI production، pairing واقعی و exclusion رسمی PRE_AI هنوز به‌طور کامل wired نشده‌اند. به همین دلیل EXP-001 هنوز NOT_READY است و فعال‌سازی مستقیم AI می‌تواند مقایسه‌ای با snapshot یا اطلاعات نابرابر ایجاد کند.

## A2. Runtime فعلی

ACTIVE: run_forecast.php از Alpaca داده می‌گیرد، guard/features/snapshot/Quant را اجرا می‌کند، FINAL immutable، target و system job ایجاد می‌کند. Outcome، Evaluation، Benchmark، FRF و report/email cronها wired هستند.

IMPLEMENTED_BUT_NOT_WIRED: OpenAIProvider، PairedEligibility و بخشی از AI snapshot agreement.

PARTIAL: cutoff مرکزی، capture مستقل Reference، PRE_AI official filters، retry کامل Alpaca و consistency جامع گزارش.

DISABLED: AI production، Trading و Paper Trading.

## A3. Runtime نهایی هدف

Market Data → Central Information Cutoff → Completed-Bar Validation → Market Snapshot → Feature Snapshot → Quant V1 و AI_QUANT_V1 از همان snapshot → دو prediction مستقل immutable با pair identity → targets → reference t0 → future outcome t1 → evaluation → benchmarks → FRF 2.0 → ViewModel مشترک → Dashboard/HTML/Email/Plain Text.

Mapping: cutoff در ForecastRun/سرویس cutoff؛ guard در CompletedMarketBarGuard؛ داده و hash در SnapshotPersistence؛ Quant در QuantForecastEngine؛ AI در OpenAIProvider؛ ارتباط در paired persistence؛ outcome در TargetTimePriceResolver و OutcomeResolver؛ گزارش در DailyReportDataBuilder، DailyReportViewModel و DailyHtmlRenderer.

## A4. فایل‌های دقیق پیشنهادی

FILES TO MODIFY:
- app/Evaluation/PriceRuleV1.php: نسخه V2 برای stock OPEN و حفظ semantics قدیمی.
- app/Evaluation/ForecastTargetFactory.php و TargetTimePriceResolver.php: اتصال ruleهای versioned.
- cron/run_forecast.php: ایجاد cutoff واحد، snapshot مشترک و اجرای gate/AI.
- app/AI/AiProductionGate.php: بررسی readiness واقعی.
- app/Reports/DailyReportDataBuilder.php و renderers: فیلتر paired و نمایش جداگانه Quant/AI.
- cron/run_frf_windows.php و benchmark queries: اعمال eligibility.
- app/Jobs/JobRunner.php: ثبت failure/gate reason.

FILES TO CREATE:
- app/Forecast/InformationCutoff.php
- app/AI/ForecastPairRepository.php
- app/AI/PairedStatistics.php
- app/Evaluation/ReferenceCaptureResolver.php
- tests/ReferencePriceRuleV2Test.php
- tests/CentralInformationCutoffTest.php
- tests/ForecastPairConstraintTest.php
- tests/PreAiExclusionTest.php
- tests/AiRetryFrozenSnapshotTest.php
- tests/PairedAiQuantE2ETest.php
- tests/ReportPairedConsistencyTest.php
- tests/MarketSnapshotAuditabilityTest.php

MIGRATIONS: فقط در صورت نبود قابلیت فعلی؛ برای pair_id/cycle_id، snapshot IDs و activation boundary migration versioned جدید ایجاد می‌شود. migration پیش از approval اجرا نمی‌شود.

برای BTC/USD و ETH/USD، CRYPTO_REFERENCE_PRICE_RULE_V1 به‌صورت مستقل تعریف می‌شود: t0 همان forecast timestamp، timeframe برابر 1Min، اولین bar معتبر در یا بلافاصله پس از t0، tolerance حداکثر 60 دقیقه، نبود bar وضعیت RETRYABLE و retry بدون تغییر t0 یا snapshot است. Outcome همچنان t1 است و actual_return=(outcome_price_t1-reference_price_t0)/reference_price_t0*100. CryptoReferencePriceRuleTest و paired offline E2E این رفتار را پوشش می‌دهند.

PriceRuleV1 تاریخی هرگز silently تغییر نمی‌کند. رکوردهای قدیمی با semantics و version اصلی قابل تفسیر می‌مانند؛ ReferencePriceRuleV2 و CRYPTO_REFERENCE_PRICE_RULE_V1 فقط برای forecastهای جدید و eligible استفاده و version آن‌ها persist می‌شود.

CRON/CLI: run_forecast و job wrapper فقط پس از wiring؛ بدون فعال‌کردن AI در این مرحله.

## A5. Database Changes

در حال حاضر ai_activation_at وجود دارد، اما pair identity و رابطه صریح دو prediction با cycle، cutoff، market_snapshot_id و feature_snapshot_id کامل نیست. migration احتمالی باید pair/cycle را با unique constraints روی experiment، asset، horizon، cycle و model source اضافه کند و activation را immutable نگه دارد. reference_price، reference_price_time، reference_rule و reference_status موجودند؛ برای stock V2 مقدار جدید فقط برای forecastهای جدید استفاده می‌شود.

## A6. ریسک و mitigation

- Look-ahead: cutoff واحد + API end boundary + guard.
- Reference leakage: stock OPEN جلسه بعد، capture پس از FINAL و rule version مستقل.
- AI newer data: AI فقط frozen snapshot را می‌گیرد و retry fetch ندارد.
- mismatch: pair constraint و exact IDs، نه timestamp تقریبی.
- PRE_AI contamination: activation + pair existence + هر دو prediction + snapshot agreement.
- historical mutation: عدم update رکوردهای قدیمی و hash immutability.
- duplicate pair: database unique identity و idempotent repository.
- invalid activation: timestamp فقط forward و پس از عبور همه gateها، بدون backdate.

## A7. Test Plan

UNIT: ruleهای Reference V2، cutoff، pair identity، gate، eligibility و retry.

INTEGRATION: schema constraints، repository conflict، shared snapshot، AI failure isolation، historical resolver و report filters.

OFFLINE E2E: fake bars → cutoff → guard → snapshots → Quant+mock AI → immutable pair → targets/reference/outcome/evaluation/benchmark/FRF/report.

PRODUCTION-SAFE SMOKE: lint، migration dry/read checks، cron listing، gate disabled behavior، بدون تولید outcome مصنوعی.

AiProductionGate در runtime باید methodology/version، cutoff معتبر، snapshot معتبر، schema/migrations، target lifecycle، reference rule، evaluation، benchmark، FRF و rolling FRF، pairing، PRE_AI filters، AI flag و credential را از وضعیت عملیاتی بررسی کند؛ تست‌های قبلی به‌تنهایی readiness production محسوب نمی‌شوند.

PASS فقط با code + wiring + persistence + test evidence؛ نبود outcome طبیعی با READY_AND_WAITING_FOR_REAL_TIME گزارش می‌شود.

## A8. Implementation Order

1. Reference methodology V2 و تست‌ها.
2. Central cutoff و propagation.
3. Pair identity/schema و constraints.
4. PRE_AI exclusion در تمام queryهای رسمی.
5. Scientific AI Gate.
6. OpenAI production wiring با frozen snapshot.
7. paired offline E2E و AI failure tests.
8. فعال‌سازی prospective EXP-001 بدون backfill.
9. smoke test و read-only final audit.

Activation آخرین کنترل است: فقط پس از موفقیت migration، full tests، paired offline E2E، runtime gate و smoke test انجام می‌شود؛ timestamp current/prospective، غیرقابل backdate و atomic با status transition خواهد بود. پس از failure timestamp بازنویسی نمی‌شود و audit trail حفظ می‌گردد.

این ترتیب ابتدا ریسک reference/cutoff را می‌بندد، سپس pairing را enforce می‌کند و فقط بعد از آن AI را به production می‌سپارد.

## A9. مواردی که حفظ می‌شوند

Quant V1 logic، فرمول‌های FRF 2.0، threshold outcome ±0.50، target logic معتبر، XAUT fail-closed، FINALهای immutable، report/email معتبر و Trading/Paper Trading disabled بدون تغییر غیرضروری حفظ می‌شوند.

## A10. Phase 1 Final Block

PLAN_VERSION:
FINAL_SCIENTIFIC_ACTIVATION_PLAN_V4_R2

CODE_CHANGED:
NO

DATABASE_CHANGED:
NO

PRODUCTION_CHANGED:
NO

READY_FOR_USER_REVIEW:
YES

WAITING_FOR_USER_APPROVAL:
YES

CRYPTO_REFERENCE_RULE_DEFINED:
YES

HISTORICAL_RULE_VERSIONING_PRESERVED:
YES

AI_GATE_RUNTIME_READINESS_DEFINED:
YES

ACTIVATION_SEQUENCE_DEFINED:
YES
