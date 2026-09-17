# گزارش ممیزی وضعیت فعلی AlpacaAIApp

این فایل نسخه قابل‌کپی‌پیست گزارش ممیزی فقط‌خواندنی است. در زمان ممیزی هیچ کد، migration، تنظیمات، Cron یا داده‌ای تغییر نکرد.

## خلاصه اجرایی

- Alpaca و Quant V1 فعال هستند.
- AI Provider وجود دارد اما در Cron production فراخوانی نمی‌شود.
- AI + Quant، Outcome Resolver، Evaluation، Benchmark و FRF واقعی در مسیر production کامل نیستند.
- Freeze و SHA-256 فعال هستند اما immutable بودن فقط application-level است.
- HTML، پنل عمومی، archive فایل‌محور و email با native `mail()` فعال‌اند.
- Trading و Paper Trading غیرفعال‌اند.
- طبقه‌بندی: `QUANT_PROTOTYPE`.

## مسیر واقعی

```text
run_forecast.php → AlpacaProvider → Indicators → QuantForecastEngine
→ quant_predictions → ForecastFreezer → final_predictions
→ aggregate_daily.php → generate_daily_report.php → send_daily_report.php
```

AI، OutcomeResolver، Evaluation، Benchmark و FRF در مسیر فعلی فراخوانی نمی‌شوند.

## Alpaca

کلاس: `app/MarketData/AlpacaProvider.php`.

سهام: `/v2/stocks/{symbol}/bars` و `/v2/stocks/{symbol}/bars/latest`.

کریپتو: `/v1beta3/crypto/us/bars` و `/v1beta3/crypto/us/latest/bars`.

Authentication با Headerهای `APCA-API-KEY-ID` و `APCA-API-SECRET-KEY`، feed برابر `iex`، timeframe برابر `1Day` و timeout برابر ۱۵ ثانیه است. HTTP، curl، JSON و فیلدهای OHLCV بررسی می‌شوند. Retry، backoff، rate-limit handling و pagination کامل وجود ندارد.

دارایی‌های سهام و کریپتو configured/fetched هستند. XAUT provider واقعی ندارد و fail-closed است.

## Feature و Quant

Featureهای استفاده‌شده: SMA20، EMA20، EMA50، RSI14، Momentum5 و Momentum20. ATR، Volatility و برخی featureهای دیگر در runner فعال نیستند.

قواعد: EMA20 بالاتر از EMA50، Close بالاتر از SMA20، RSI و Momentum امتیاز می‌دهند. `score >= 2` صعودی، `score <= -2` نزولی و بقیه خنثی‌اند. Confidence برابر `min(1.0, 0.5 + abs(score)/10)` است.

`expected_return_pct` فعلی score است، نه بازده درصدی واقعی؛ ریسک علمی HIGH.

## Cutoff و Reference Price

`information_cutoff` در `final_predictions` وجود دارد و از timestamp آخرین bar گرفته می‌شود، اما کامل بودن bar روز جاری سهام صریحاً enforce نشده است؛ احتمال leakage از bar ناقص وجود دارد: CRITICAL.

Snapshot کامل بازار و hash آن در runner ذخیره نمی‌شود.

## AI

`OpenAIProvider` و `ForecastSchemaValidator` وجود دارند، اما `run_forecast.php` آن‌ها را فراخوانی نمی‌کند. وضعیت: `IMPLEMENTED_NOT_WIRED`. Fusion و paired comparison فعال نیستند.

## Outcome، Evaluation و Benchmark

`OutcomeResolver` فقط متدهای محاسبه return و direction دارد و job production ندارد. threshold پیش‌فرض direction صفر است، نه ±۰.۵٪.

`MetricsCalculator` correct/wrong، absolute error، squared error، Brier و RMSE دارد؛ اما در Cron فعلی اجرا نمی‌شود. BenchmarkEngine نیز wired نیست.

## FRF 2.0

`FrfCalculator` محلی FPS، ECS و FRS را دارد. وزن FPS: direction `.25`، probability `.15`، calibration `.15`، return error `.15`، benchmark edge `.20` و consistency `.10`. وزن ECS: sample size `.35`، time coverage `.20`، coverage `.15`، regime coverage `.15` و data quality `.15`.

```text
FRS = FPS * (0.5 + 0.5 * ECS / 100)
```

در دیتابیس خصوصی هاست `reliability_runs` و `reliability_scores` موجود نیستند؛ پس FRF معتبر production نداریم.

## HTML، پنل و ایمیل

HTML فارسی، RTL، UTF-8، header تیره، کارت‌های خلاصه، FRF، لینک پنل و escaping دارد؛ اما DataBuilder/ViewModel کامل نیستند و Forecast/Resolved/FRF واقعی فعلاً خالی یا unavailable هستند.

پنل: `https://rayanhost.org/AlpacaAIApp/public/reports.php`.

آرشیوها نزولی مرتب می‌شوند و با `report.php?date=YYYY-MM-DD` عمومی قابل مشاهده‌اند. Email با native `mail()`، گیرنده `afmobile49@gmail.com` و duplicate prevention فعال است؛ retry کامل FAILED وجود ندارد.

## وضعیت دیتابیس هاست

```text
assets=8
experiments=1
forecast_runs=1
quant_predictions=7
ai_predictions=0
final_predictions=28
forecast_targets=0
actual_outcomes=0
evaluation_results=0
benchmark_results=0
reliability_runs=MISSING
reliability_scores=MISSING
report_archives=0
report_deliveries=3
system_jobs=0
daily_aggregates=2
```

## امنیت و Trading

API key/secret در source یا HTML پیدا نشدند. `.env` خصوصی است و SQLite طبق runtime خارج از public root است. گزارش‌ها عمومی هستند چون کاربر تأیید کرده است. Order submission فعال وجود ندارد؛ Trading و Paper Trading غیرفعال‌اند.

## تست‌ها

```text
Total: 37
Passed: 36
Failed: 1
Skipped: 0
```

Failure: `ReportsPageTest::testReportsPageEscapesOutput`؛ تست قدیمی انتظار متن انگلیسی `Read-only` دارد و پنل فعلی فارسی/RTL است.

## Confirmed Successful Executions

دریافت Alpaca، اجرای Quant V1، ثبت Quant/Final Prediction، Freeze و SHA-256، aggregation، تولید HTML، ارسال email با `SENT`، duplicate با `SKIPPED`، health با `ok`، پنل و archive با HTTP 200 و مرتب‌سازی نزولی تأیید شده‌اند.

## Unproven or Not Yet Executed

AI production، AI + Quant fusion، OutcomeResolver، Evaluation، Benchmark aggregation، FRF production، rolling FRF، regime analysis، paired fairness، چند روز forward validation و تحویل نهایی Inbox هنوز اثبات نشده‌اند.

## اختلاف‌های مهم با specification

1. جداول FRF در production نیستند: CRITICAL.
2. AI در Cron فراخوانی نمی‌شود: HIGH.
3. Target و Outcome ایجاد نمی‌شوند: CRITICAL.
4. threshold direction صفر است، نه ±۰.۵٪: HIGH.
5. expected return برابر score است: HIGH.
6. snapshot کامل بازار ذخیره نمی‌شود: HIGH.
7. archive metadata در `report_archives` persistence نشده: MEDIUM.

## Completion Matrix خلاصه

| Component | Status |
|---|---|
| Alpaca Stocks | PASS |
| Alpaca Crypto | PARTIAL |
| XAUT | PARTIAL |
| Information Cutoff | PARTIAL |
| Feature Engine | PARTIAL |
| Quant V1 | PASS |
| AI Provider | PARTIAL |
| AI + Quant | NOT_IMPLEMENTED |
| Forecast Freeze | PASS |
| Forecast Hash | PASS |
| Feature Snapshot | PARTIAL |
| Forecast Targets | NOT_IMPLEMENTED |
| Outcome Resolver | NOT_IMPLEMENTED |
| Evaluation | PARTIAL |
| Benchmarks | PARTIAL |
| FPS / ECS / FRS | PARTIAL |
| Rolling FRF | NOT_IMPLEMENTED |
| Daily Report | PARTIAL |
| Dashboard | PARTIAL |
| Email | PASS |
| Archive | PARTIAL |
| Security | PARTIAL |
| Trading Safety | PASS |

## Top 10 gaps

1. Outcome Target و Outcome Resolver فعال نیست.
2. جداول FRF production وجود ندارند.
3. AI در Cron فعال نیست.
4. AI + Quant paired comparison وجود ندارد.
5. bar ناقص روز جاری ممکن است وارد شود.
6. expected return واقعی نیست.
7. threshold outcome ناسازگار است.
8. Evaluation و Benchmark production فعال نیستند.
9. snapshot کامل بازار وجود ندارد.
10. ViewModel و report builder ناقص‌اند.

## امتیاز آمادگی

- Architecture implementation completeness: `5.2 / 10`
- Scientific validity readiness: `2.8 / 10`
- Production operational readiness: `5.0 / 10`
- Reporting/observability readiness: `5.8 / 10`
- OVERALL CURRENT-STATE SCORE: `4.7 / 10`

طبقه‌بندی: `QUANT_PROTOTYPE`.

```text
CURRENT_STATE_VERSION: AUDIT_V1
PROJECT: AlpacaAIApp
CODE_CHANGED: NO
DATABASE_CHANGED: NO
PRODUCTION_CONFIG_CHANGED: NO
TRADING_CHANGED: NO
AI_ACTIVATION_CHANGED: NO
OVERALL_STATUS: QUANT_PROTOTYPE
CRITICAL_GAPS: 3
HIGH_GAPS: 7
TESTS_TOTAL: 37
TESTS_PASSED: 36
TESTS_FAILED: 1
TESTS_SKIPPED: 0
READY_FOR_COMPLETION_SPEC: NO
BLOCKING_REASON: AI production wiring، target/outcome/evaluation chain، FRF persistence و تضمین کامل information cutoff فعال نیستند.
```
