AlpacaAIApp — FRF 2.0
سند مکمل پیاده‌سازی و اتصال Forecast Reliability Framework 2.0
به پروژه AI Market Forecast Validation System — PHP/SQLite/cPanel/Codex v1.0


نسخه سند: 2.0
نوع سند: Integration Specification / Codex Companion Specification
پروژه مادر: AlpacaAIApp — AI Market Forecast Validation System
سند مرتبط قبلی:
«راهنمای پیاده‌سازی سیستم ارزیابی پیش‌بینی بازار با PHP و Codex — نسخه 1.0»
Google Drive File ID: 1WGcN21HZhf4d7Itycm2XoDBxrCodGxSitpu1tLNwNJs


============================================================
1. هدف این سند
============================================================


این سند مکمل مستقیم سند پیاده‌سازی نسخه 1.0 است و باید توسط Codex به‌عنوان Extension Specification خوانده شود، نه جایگزین سند قبلی.


هدف:
افزودن یک لایه علمی، versioned، قابل‌ممیزی و deterministic برای سنجش کیفیت پیش‌بینی‌های AlpacaAIApp در طول زمان.


نام رسمی چارچوب:
Forecast Reliability Framework 2.0 (FRF 2.0)


سه شاخص رسمی:
1) FPS — Forecast Performance Score
2) ECS — Evidence Confidence Score
3) FRS — Forecast Reliability Score


اصل مهم:
FRF 2.0 نباید هیچ تغییری در پیش‌بینی‌های گذشته ایجاد کند، نباید outcome را با AI قضاوت کند و نباید به هیچ شکل نتیجه را از روی hindsight اصلاح کند.


FRF 2.0 فقط روی forecastهای FINAL و outcomeهای RESOLVED کار می‌کند.


============================================================
2. رابطه این سند با پروژه قبلی
============================================================


Codex باید ابتدا پروژه‌ای را که از سند v1.0 ساخته شده شناسایی کند و سپس این سند را به همان codebase اضافه کند.


این سند فرض می‌کند اجزای زیر از پروژه قبلی وجود دارند یا در حال ساخته‌شدن هستند:


- PHP 8.4
- SQLite
- cPanel Cron
- assets
- experiments
- forecast_runs
- quant_predictions
- ai_predictions
- final_predictions
- forecast_targets
- actual_outcomes
- evaluation_results
- benchmark_results
- model_versions
- prompt_versions
- system_jobs
- dashboard
- OutcomeResolver
- MetricsCalculator
- BenchmarkEngine
- ForecastFreezer


قانون integration:
هیچ جدول یا کلاس موجود صرفاً برای FRF 2.0 حذف نشود.
تغییر schema فقط به‌صورت migration جدید انجام شود.
Migration قبلی دست‌کاری نشود.


Migration جدید پیشنهادی:
database/migrations/002_frf_v2.sql


Namespace پیشنهادی:
App\Reliability\


============================================================
3. مسئله‌ای که FRF 2.0 حل می‌کند
============================================================


یک Accuracy ساده کافی نیست.


مثال:
مدل A:
Accuracy = 70%
N = 30


مدل B:
Accuracy = 64%
N = 4,000


عدد 70% الزاماً قابل‌اعتمادتر نیست.


همچنین:
Accuracy = 64%
اما Always Bullish = 63%


در این حالت مدل ارزش افزوده قابل‌توجهی ایجاد نکرده است.


FRF 2.0 این موارد را از هم جدا می‌کند:


A) کیفیت عملکرد مدل
B) قدرت شواهدی که آن نتیجه را پشتیبانی می‌کند
C) اعتبار نهایی روش با توجه به هر دو مورد


============================================================
4. تعریف رسمی شاخص‌ها
============================================================


4.1 FPS — Forecast Performance Score


سؤال:
«روش پیش‌بینی، روی داده‌های آینده‌ای که واقعاً رخ داده‌اند، چقدر خوب عمل کرده است؟»


مقیاس:
0 تا 100


FPS از شش جزء ساخته می‌شود:


- Direction Score
- Probability Score
- Calibration Score
- Return Error Score
- Benchmark Edge Score
- Consistency Score


وزن‌های نسخه FRF_2_0:


Direction Score        25%
Probability Score      15%
Calibration Score      15%
Return Error Score     15%
Benchmark Edge Score   20%
Consistency Score      10%


فرمول:


FPS =
0.25 * DirectionScore +
0.15 * ProbabilityScore +
0.15 * CalibrationScore +
0.15 * ReturnErrorScore +
0.20 * BenchmarkEdgeScore +
0.10 * ConsistencyScore


خروجی نهایی clamp به بازه 0..100.


------------------------------------------------------------
4.1.1 Direction Score
------------------------------------------------------------


برای جلوگیری از فریب ناشی از class imbalance، معیار اصلی ترجیحاً Balanced Accuracy است.


کلاس‌ها:
BULLISH
NEUTRAL
BEARISH


برای هر کلاس:
Recall(class) = correct predictions of class / actual occurrences of class


BalancedAccuracy =
mean(recall_bullish, recall_neutral, recall_bearish)
فقط کلاس‌هایی که در window حداقل MIN_CLASS_SUPPORT رخ داده‌اند وارد میانگین شوند.


V1 config:
MIN_CLASS_SUPPORT = 5


DirectionScore = 100 * BalancedAccuracy


اگر هیچ کلاس support کافی نداشت:
DirectionScore = NULL
و FPS به‌صورت INSUFFICIENT_DATA علامت بخورد.


همراه آن Raw Directional Accuracy هم برای گزارش ذخیره شود، اما امتیاز FPS از Balanced Accuracy استفاده کند.


------------------------------------------------------------
4.1.2 Probability Score
------------------------------------------------------------


از Brier Score سه‌کلاسه استفاده شود.


برای هر prediction:
BS = sum((p_k - o_k)^2) برای سه کلاس


برای سه کلاس، دامنه نظری BS بین 0 و 2 است.


NormalizedBrier = clamp(BS_mean / 2, 0, 1)


ProbabilityScore =
100 * (1 - NormalizedBrier)


هرچه بهتر، عدد بالاتر.


------------------------------------------------------------
4.1.3 Calibration Score
------------------------------------------------------------


هدف:
بررسی اینکه confidence اعلام‌شده با نرخ موفقیت واقعی همخوانی دارد یا نه.


برای هر prediction:
predicted_confidence = max(probability_up, probability_neutral, probability_down)
predicted_class = argmax(probabilities)
correct = 1 اگر predicted_class == actual_direction وگرنه 0


Bucketها:
0.50–0.60
0.60–0.70
0.70–0.80
0.80–0.90
0.90–1.00


ECE:
Expected Calibration Error


ECE =
sum_over_buckets(
    bucket_weight *
    abs(bucket_mean_confidence - bucket_actual_accuracy)
)


CalibrationScore =
100 * (1 - clamp(ECE / 0.25, 0, 1))


منطق:
ECE برابر یا بیشتر از 0.25 => CalibrationScore = 0
ECE = 0 => CalibrationScore = 100


اگر bucket کمتر از 5 نمونه داشت، در ECE window-level وارد نشود و low_support علامت بخورد.


------------------------------------------------------------
4.1.4 Return Error Score
------------------------------------------------------------


برای دارایی‌های متفاوت، خطای درصدی خام به‌تنهایی قابل مقایسه نیست.


از volatility-scaled absolute error استفاده شود.


برای هر prediction:


AbsoluteReturnError =
abs(expected_return_pct - actual_return_pct)


Scale =
max(realized_volatility_for_horizon_pct, MIN_ERROR_SCALE_PCT)


V1:
MIN_ERROR_SCALE_PCT = 0.50


ScaledError =
AbsoluteReturnError / Scale


MeanScaledError =
mean(ScaledError)


ReturnErrorScore =
100 * exp(-MeanScaledError)


اگر expected_return_pct موجود نباشد:
آن prediction در ReturnErrorScore وارد نشود.
coverage آن جدا ثبت شود.


------------------------------------------------------------
4.1.5 Benchmark Edge Score
------------------------------------------------------------


هدف:
مدل باید فقط «خوب» نباشد؛ باید نسبت به baselineهای ساده ارزش افزوده داشته باشد.


Primary benchmark:
QUANT_V1 برای AI_QUANT
و برای QUANT_V1، بهترین baseline ساده معتبر از موارد زیر:
ALWAYS_BULLISH
PREVIOUS_SESSION_DIRECTION
MOMENTUM_5
MOMENTUM_20
SMA20_VS_SMA50


برای AI_QUANT:
PrimaryEdgePP =
100 * (BalancedAccuracy_AI - BalancedAccuracy_QUANT)


BenchmarkEdgeScore =
clamp(50 + 5 * PrimaryEdgePP, 0, 100)


تفسیر:
Edge = 0pp      => 50
Edge = +5pp     => 75
Edge = +10pp    => 100
Edge = -5pp     => 25
Edge = -10pp    => 0


همچنین Secondary Edge نسبت به best-simple-baseline ذخیره شود.


اگر benchmark متناظر داده کافی ندارد:
BenchmarkEdgeScore = NULL
و FPS باید status=PARTIAL_EVIDENCE بگیرد.


------------------------------------------------------------
4.1.6 Consistency Score
------------------------------------------------------------


هدف:
مدل نباید فقط در یک asset یا یک زیر‌بازه خوب باشد.


Window به subgroups زیر شکسته شود:
- asset
- horizon
- rolling subperiods


برای هر subgroup که حداقل MIN_GROUP_N دارد:
BalancedAccuracy آن subgroup محاسبه شود.


V1:
MIN_GROUP_N = 20


StdDevPP =
standard deviation of subgroup balanced accuracies in percentage points


ConsistencyScore =
clamp(100 - 2.5 * StdDevPP, 0, 100)


مثال:
stddev=4pp => 90
stddev=10pp => 75
stddev=20pp => 50


اگر subgroup کافی نیست:
ConsistencyScore = NULL
و وضعیت insufficient_subgroups ثبت شود.


============================================================
5. ECS — Evidence Confidence Score
============================================================


سؤال:
«چقدر شواهد کافی و متنوع داریم که بتوانیم به FPS فعلی اعتماد کنیم؟»


مقیاس:
0 تا 100


ECS از پنج جزء ساخته می‌شود:


- Sample Size Score       35%
- Time Coverage Score     20%
- Asset/Horizon Coverage  15%
- Regime Coverage Score   15%
- Data Quality Score      15%


فرمول:


ECS =
0.35 * SampleSizeScore +
0.20 * TimeCoverageScore +
0.15 * CoverageScore +
0.15 * RegimeCoverageScore +
0.15 * DataQualityScore


------------------------------------------------------------
5.1 Sample Size Score
------------------------------------------------------------


از تابع saturating استفاده شود تا N بسیار بزرگ بی‌نهایت امتیاز نسازد.


TargetResolvedN = 2000


SampleSizeScore =
100 * (1 - exp(-N / TargetResolvedN))


نتیجه تقریبی:
N=100   => ~4.9
N=500   => ~22.1
N=1000  => ~39.3
N=2000  => ~63.2
N=4000  => ~86.5
N=6000  => ~95.0


این score عمداً سخت‌گیرانه است.


------------------------------------------------------------
5.2 Time Coverage Score
------------------------------------------------------------


TargetDays = 180


TimeCoverageScore =
100 * min(observed_calendar_days / 180, 1)


اگر experiment فقط 30 روز عمر دارد:
~16.7


اگر 90 روز:
50


اگر 180 روز یا بیشتر:
100


------------------------------------------------------------
5.3 Asset/Horizon Coverage Score
------------------------------------------------------------


Expected cells در EXP-001:


Stocks:
5 assets * 3 horizons = 15 cells


Crypto/XAUT:
3 assets * 3 horizons = 9 cells


Total = 24 asset-horizon cells


یک cell covered محسوب شود اگر حداقل:
MIN_CELL_N = 20 resolved predictions


CoverageRatio =
covered_cells / expected_enabled_cells


CoverageScore = 100 * CoverageRatio


اگر asset به‌علت provider unavailable به‌صورت رسمی disabled باشد:
از denominator آن experiment حذف شود، اما reason ثبت شود.


------------------------------------------------------------
5.4 Regime Coverage Score
------------------------------------------------------------


در FRF 2.0 از ابتدا قابلیت regime-aware تعریف شود.


حداقل regimeهای V1:
TREND_UP
TREND_DOWN
SIDEWAYS
HIGH_VOLATILITY


هر regime covered محسوب شود اگر:
MIN_REGIME_N = 50


RegimeCoverageScore =
100 * covered_regimes / enabled_regimes


اگر Regime Classifier هنوز در codebase فعال نشده:
RegimeCoverageScore = 50
و status = NOT_YET_CLASSIFIED


این مقدار 50 یعنی neutral، نه evidence کامل.


------------------------------------------------------------
5.5 Data Quality Score
------------------------------------------------------------


موارد:
- missing reference price
- missing outcome
- provider fallback
- stale data
- invalid/void predictions
- hash mismatch
- unresolved stale target


ValidResolved =
resolved predictions that pass all integrity checks


DataQualityRate =
ValidResolved / all matured predictions


DataQualityScore =
100 * DataQualityRate


Hard penalties:
اگر هر hash mismatch معتبر وجود داشت:
DataQualityScore حداکثر 60


اگر forecast پس از FINAL تغییر کرده باشد:
FRF window status = INTEGRITY_BREACH
و FRS منتشر نشود تا بررسی شود.


============================================================
6. FRS — Forecast Reliability Score
============================================================


FRS امتیاز خلاصه اعتبار روش است.


اصل:
ECS نباید مثل یک metric عملکردی با FPS صرفاً average شود.
ECS نقش «قدرت evidence» را دارد.


فرمول FRF 2.0:


EvidenceFactor =
0.50 + 0.50 * (ECS / 100)


FRS_raw =
FPS * EvidenceFactor


سپس:


FRS = clamp(FRS_raw, 0, 100)


منطق:
اگر ECS=0، مدل حداکثر 50% از FPS خود را به‌عنوان reliability دریافت می‌کند.
اگر ECS=100، FRS=FPS.
اگر ECS=50، FRS=0.75*FPS.


مثال:
FPS=90, ECS=30
EvidenceFactor=0.65
FRS=58.5


تفسیر:
عملکرد ظاهری عالی است، اما شواهد هنوز ضعیف است.


مثال:
FPS=78, ECS=93
EvidenceFactor=0.965
FRS=75.3


در Dashboard هر سه عدد نمایش داده شوند؛ FRS هرگز جای FPS/ECS را نگیرد.


============================================================
7. سطح‌بندی رسمی Scoreها
============================================================


برای FPS:
0–39   POOR
40–54  WEAK
55–69  MODERATE
70–84  GOOD
85–100 EXCELLENT


برای ECS:
0–39   WEAK_EVIDENCE
40–59  LIMITED_EVIDENCE
60–79  MODERATE_EVIDENCE
80–100 STRONG_EVIDENCE


برای FRS:
0–39   UNRELIABLE
40–54  PROVISIONAL
55–69  MODERATE
70–84  RELIABLE
85–100 HIGH_RELIABILITY


قانون:
اگر ECS < 40:
FRS label حداکثر PROVISIONAL باشد، حتی اگر عدد FRS بالاتر افتاد.


اگر integrity breach:
FRS label = SUSPENDED


============================================================
8. Cumulative و Rolling Windows
============================================================


FRF 2.0 باید همزمان این windowها را محاسبه کند:


- CUMULATIVE
- ROLLING_90D
- ROLLING_30D
- ROLLING_7D


CUMULATIVE:
تمام forecastهای RESOLVED همان experiment از روز اول تا now.


Rolling:
فقط outcomeهایی که actual_price_time داخل window است.


نکته:
FRS میانگین روزانه FRSها نیست.
هر window باید از predictionهای خام resolved مربوط به همان window مجدداً محاسبه شود.


============================================================
9. Trend Detection
============================================================


هدف:
تشخیص افت یا بهبود اخیر بدون overreaction.


مقایسه اصلی:
FRS_30D vs FRS_90D


Delta = FRS_30D - FRS_90D


Rule:
Delta >= +7     => IMPROVING
-7 < Delta < +7 => STABLE
Delta <= -7     => WEAKENING


اما اگر ECS_30D < 40:
trend_confidence = LOW
و پیام Dashboard باید این را صریح بگوید.


مثال:
30D FRS = 58
90D FRS = 76
Delta = -18
Trend = WEAKENING


اگر ECS_30D = 32:
Interpretation:
"Recent performance is weaker, but short-window evidence is still limited."


============================================================
10. Confidence Interval
============================================================


برای Directional Accuracy و Balanced Accuracy، 95% CI گزارش شود.


برای Raw Accuracy:
Wilson score interval استفاده شود.


برای Balanced Accuracy:
Bootstrap confidence interval با deterministic seed پیشنهادی:
seed = hash(experiment_id + window_code + source_type + calculation_date)


V1 bootstrap iterations:
1000


اگر اجرای bootstrap روی shared host سنگین بود:
Codex می‌تواند CI را در nightly aggregation محاسبه و cache کند، نه در request dashboard.


CI نباید مستقیماً به FPS اضافه شود؛ width آن در ECS یا diagnostics نمایش داده شود.


پیشنهاد V1:
CI width فقط diagnostic.
در FRF 2.1 می‌تواند component مستقل شود.


============================================================
11. Market Regime Stability
============================================================


FRF 2.0 باید schema را برای regime metrics آماده کند.


جدول یا field پیشنهادی:
market_regime


هر forecast در زمان Information Cutoff یک regime label می‌گیرد.


در V1 اگر classifier هنوز آماده نیست:
regime = UNCLASSIFIED


پس از فعال‌سازی:
TREND_UP
TREND_DOWN
SIDEWAYS
HIGH_VOLATILITY


Dashboard باید FRS/FPS per regime را نمایش دهد وقتی support کافی است.


============================================================
12. Schema Changes
============================================================


Migration:
002_frf_v2.sql


جدول 1: reliability_runs


CREATE TABLE reliability_runs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    experiment_id INTEGER NOT NULL,
    source_type TEXT NOT NULL,
    window_code TEXT NOT NULL,
    window_start TEXT,
    window_end TEXT NOT NULL,
    calculation_version TEXT NOT NULL,
    status TEXT NOT NULL,
    resolved_n INTEGER NOT NULL,
    created_at TEXT NOT NULL,
    UNIQUE(experiment_id, source_type, window_code, window_end, calculation_version)
);


جدول 2: reliability_scores


CREATE TABLE reliability_scores (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reliability_run_id INTEGER NOT NULL UNIQUE,


    fps REAL,
    ecs REAL,
    frs REAL,


    direction_score REAL,
    probability_score REAL,
    calibration_score REAL,
    return_error_score REAL,
    benchmark_edge_score REAL,
    consistency_score REAL,


    sample_size_score REAL,
    time_coverage_score REAL,
    coverage_score REAL,
    regime_coverage_score REAL,
    data_quality_score REAL,


    raw_accuracy REAL,
    balanced_accuracy REAL,
    brier_score REAL,
    ece REAL,
    mean_scaled_error REAL,
    primary_edge_pp REAL,


    ci95_low REAL,
    ci95_high REAL,


    fps_label TEXT,
    ecs_label TEXT,
    frs_label TEXT,
    trend_label TEXT,
    trend_confidence TEXT,


    diagnostics_json TEXT NOT NULL,
    created_at TEXT NOT NULL,


    FOREIGN KEY(reliability_run_id) REFERENCES reliability_runs(id)
);


جدول 3: reliability_group_scores


CREATE TABLE reliability_group_scores (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reliability_run_id INTEGER NOT NULL,
    group_type TEXT NOT NULL,
    group_key TEXT NOT NULL,
    resolved_n INTEGER NOT NULL,
    fps REAL,
    ecs REAL,
    frs REAL,
    balanced_accuracy REAL,
    raw_accuracy REAL,
    diagnostics_json TEXT,
    UNIQUE(reliability_run_id, group_type, group_key),
    FOREIGN KEY(reliability_run_id) REFERENCES reliability_runs(id)
);


group_type:
ASSET
HORIZON
REGIME
ASSET_HORIZON


جدول 4: forecast_regimes


CREATE TABLE forecast_regimes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    final_prediction_id INTEGER NOT NULL UNIQUE,
    regime_code TEXT NOT NULL,
    classifier_version TEXT NOT NULL,
    classified_at TEXT NOT NULL,
    metadata_json TEXT,
    FOREIGN KEY(final_prediction_id) REFERENCES final_predictions(id)
);


============================================================
13. کلاس‌های PHP جدید
============================================================


app/Reliability/
    FrfCalculator.php
    FpsCalculator.php
    EcsCalculator.php
    FrsCalculator.php
    DirectionScoreCalculator.php
    BrierScoreCalculator.php
    CalibrationScoreCalculator.php
    ReturnErrorScoreCalculator.php
    BenchmarkEdgeCalculator.php
    ConsistencyScoreCalculator.php
    EvidenceCoverageCalculator.php
    ConfidenceIntervalCalculator.php
    ReliabilityWindow.php
    ReliabilityLabeler.php
    ReliabilityTrendDetector.php
    ReliabilityRepository.php
    ReliabilityService.php


پیشنهاد interface:


interface ScoreCalculatorInterface
{
    public function calculate(array $context): ScoreResult;
}


تمام Calculatorها:
- deterministic
- unit-tested
- بدون API call
- بدون LLM call


ReliabilityService می‌تواند data را از repositories بگیرد و componentها را orchestrate کند.


============================================================
14. تغییرات در Evaluation Pipeline
============================================================


Pipeline قبلی:


Forecast
→ Outcome
→ Evaluation
→ Benchmark


Pipeline جدید:


Forecast
→ Outcome
→ Evaluation
→ Benchmark
→ FRF Aggregation
→ Dashboard / Daily Report


FRF نباید روی هر prediction به‌صورت synchronous سنگین اجرا شود.


پیشنهاد:
بعد از resolve_outcomes:
یک flag/event ثبت شود که metrics dirty شده‌اند.


Cron:
calculate_reliability.php


زمان:
پس از تکمیل outcome resolution روزانه.


وظیفه:
- محاسبه CUMULATIVE
- 90D
- 30D
- 7D
- per asset
- per horizon
- per regime (اگر available)
- ذخیره cache در reliability_scores


============================================================
15. Cron پیشنهادی
============================================================


cron/calculate_reliability.php


نمونه:
30 18 * * * /usr/local/bin/php /absolute/path/cron/calculate_reliability.php


اما timezone server ممکن است متفاوت باشد.
طبق معماری قبلی:
cron فقط wake-up کند.
PHP باید due logic و timezone را مدیریت کند.


Job key:
frf:EXP-001:AI_QUANT:YYYY-MM-DD
frf:EXP-001:QUANT:YYYY-MM-DD


Idempotent.


============================================================
16. Daily HTML / Email Integration
============================================================


گزارش روزانه:
«گزارش روزانه پیش‌بینی بازار AlpacaAIApp»


باید بخش FRF 2.0 را داشته باشد:


Forecast Reliability Framework 2.0


Overall / Cumulative:
FPS
ECS
FRS


Rolling:
FRS 90D
FRS 30D
FRS 7D


Trend:
IMPROVING / STABLE / WEAKENING


Evidence:
ECS label


نمونه:


FRF 2.0
FPS: 78 / 100 — GOOD
ECS: 93 / 100 — STRONG_EVIDENCE
FRS: 75 / 100 — RELIABLE


90D FRS: 73
30D FRS: 66
7D FRS: 58
Trend: WEAKENING
Trend Confidence: MODERATE


قاعده ایمیل:
خلاصه باشد.
جزئیات کامل در Dashboard.


============================================================
17. Dashboard Integration
============================================================


Overview cardها:


- FPS
- ECS
- FRS
- FRS Trend
- Resolved N
- Accuracy
- AI Edge
- Calibration


صفحه جدید:
Reliability


Sections:
1. Overall FRF
2. Window Comparison
3. By Asset
4. By Horizon
5. By Regime
6. Calibration
7. Evidence Strength
8. Diagnostics


API داخلی یا Repository query باید JSON مناسب view تولید کند.


نمونه JSON:


{
  "framework_version": "FRF_2_0",
  "experiment": "EXP-001",
  "source_type": "AI_QUANT",
  "cumulative": {
    "fps": 78.0,
    "ecs": 93.0,
    "frs": 75.3,
    "label": "RELIABLE"
  },
  "windows": {
    "90D": {"frs": 73.0, "ecs": 88.0},
    "30D": {"frs": 66.0, "ecs": 61.0},
    "7D": {"frs": 58.0, "ecs": 31.0}
  },
  "trend": {
    "label": "WEAKENING",
    "confidence": "LOW"
  }
}


============================================================
18. Data Integrity Rules
============================================================


FRF 2.0 فقط داده‌هایی را استفاده کند که:


- final_prediction.status = FINAL
- target = RESOLVED
- actual_outcome موجود
- forecast_hash معتبر
- reference_price معتبر
- actual_price معتبر
- outcome timestamp بعد از forecast timestamp
- experiment یکسان
- methodology version قابل ردیابی


VOID prediction وارد score نشود.


اگر final forecast hash mismatch:
INTEGRITY_BREACH


اگر timestamp anomaly:
prediction excluded
diagnostic event


اگر outcome قبل از forecast:
hard data error


============================================================
19. Versioning
============================================================


ثابت نسخه:


FRF_VERSION = "FRF_2_0"


هر reliability_run باید calculation_version داشته باشد.


اگر وزن یا فرمول عوض شد:
FRF_2_1 یا FRF_3_0


هرگز scoreهای قدیمی با فرمول جدید overwrite نشوند.


در Dashboard نسخه کنار score نشان داده شود.


============================================================
20. Codex Integration Contract
============================================================


Codex باید این سند را مکمل سند v1.0 بداند.


Source-of-truth order:


1. سند مادر:
«راهنمای پیاده‌سازی سیستم ارزیابی پیش‌بینی بازار با PHP و Codex — نسخه 1.0»


2. این سند:
«AlpacaAIApp — FRF 2.0 — سند مکمل پیاده‌سازی و اتصال به Forecast Validation PHP/Codex v1.0»


Rule:
اگر تعارضی فقط درباره reliability/evaluation aggregation وجود داشت، این سند جدید بر بخش FRF مقدم است.
اگر تعارضی درباره core forecast generation, market data, immutable forecasts, security, hosting وجود داشت، سند مادر معتبر است مگر این سند صریحاً override کرده باشد.


FRF 2.0 نباید:
- forecast generation را تغییر دهد
- AI prompt prediction را تغییر دهد
- market data provider را تغییر دهد
- trading اضافه کند
- Paper Trading اضافه کند
- immutable rule را تضعیف کند


============================================================
21. Codex Tasks
============================================================


FRF-TASK-01
Read existing codebase and map current evaluation tables/classes.
No code changes until mapping complete.


FRF-TASK-02
Add migration 002_frf_v2.sql.
Run migration tests.


FRF-TASK-03
Implement core score calculators:
Direction
Brier
Calibration
Return Error
Benchmark Edge
Consistency


FRF-TASK-04
Implement ECS components.


FRF-TASK-05
Implement FPS/ECS/FRS orchestrators and labels.


FRF-TASK-06
Implement confidence intervals.


FRF-TASK-07
Implement cumulative/rolling windows.


FRF-TASK-08
Implement reliability repositories and persistence.


FRF-TASK-09
Add calculate_reliability cron.


FRF-TASK-10
Integrate Dashboard.


FRF-TASK-11
Integrate daily HTML/email.


FRF-TASK-12
Add regression tests proving old forecast/evaluation behavior is unchanged.


FRF-TASK-13
Run full test suite and produce integration report.


============================================================
22. Acceptance Tests
============================================================


1. Migration روی DB موجود بدون حذف داده اجرا شود.
2. Existing v1 tests همچنان pass باشند.
3. FPS deterministic باشد.
4. ECS deterministic باشد.
5. FRS deterministic باشد.
6. Same input => same scores.
7. Cumulative از تمام resolved forecasts همان experiment استفاده کند.
8. 7D/30D/90D فقط window مربوط را استفاده کنند.
9. FRS میانگین FRSهای روزانه نباشد.
10. Benchmark edge درست محاسبه شود.
11. AI_QUANT بدون benchmark معتبر status مناسب بگیرد.
12. Hash mismatch باعث integrity breach شود.
13. VOID forecast وارد metric نشود.
14. Missing outcome به‌صورت resolved فرض نشود.
15. ECS با N بیشتر در شرایط برابر کاهش پیدا نکند.
16. 30-day trend با 90-day مقایسه شود.
17. Low ECS باعث trend_confidence پایین شود.
18. Dashboard نسخه FRF را نمایش دهد.
19. Email خلاصه FRF را نمایش دهد.
20. هیچ endpoint معامله اضافه نشود.
21. هیچ API secret در output نباشد.
22. Runtime قابل اجرا روی PHP 8.4 + SQLite + cPanel باشد.


============================================================
23. Unit Test Cases ضروری
============================================================


Case A:
100 prediction
همه درست
probabilities calibrated
benchmark ضعیف
انتظار: FPS بالا


Case B:
30 prediction
accuracy بالا
انتظار:
FPS ممکن است بالا باشد
ECS پایین
FRS محدود شود


Case C:
4000 prediction
accuracy متوسط ولی edge مثبت و calibration خوب
انتظار:
ECS بالا
FRS پایدارتر


Case D:
accuracy 65%
benchmark 66%
انتظار:
BenchmarkEdgeScore < 50


Case E:
confidence 90%
actual accuracy 60%
انتظار:
CalibrationScore افت واضح


Case F:
hash mismatch
انتظار:
INTEGRITY_BREACH
FRS not publishable


Case G:
7D low sample
30D/90D strong
انتظار:
7D ECS پایین
trend_confidence پایین


Case H:
same data run twice
انتظار:
identical output
no duplicate reliability rows


============================================================
24. Performance Constraints
============================================================


Shared hosting constraints:


- dashboard نباید scoreهای سنگین را live recompute کند.
- FRF calculations در Cron انجام و cache شوند.
- Dashboard فقط latest persisted reliability_scores را بخواند.
- Bootstrap CI nightly اجرا شود.
- queryها index داشته باشند.
- transactionها کوتاه باشند.
- WAL حفظ شود.


Index پیشنهادی:


CREATE INDEX idx_final_predictions_experiment_source
ON final_predictions(experiment_id, source_type, generated_at);


CREATE INDEX idx_actual_outcomes_time
ON actual_outcomes(actual_price_time);


CREATE INDEX idx_eval_prediction
ON evaluation_results(final_prediction_id);


CREATE INDEX idx_reliability_lookup
ON reliability_runs(experiment_id, source_type, window_code, window_end);


============================================================
25. Daily Interpretation Rules
============================================================


تفسیر انسانی باید deterministic باشد، نه LLM-dependent.


نمونه rules:


اگر FRS >= 70 و ECS >= 80:
"روش در حال حاضر شواهد قوی و عملکرد قابل‌اعتماد نشان می‌دهد."


اگر FPS >= 75 و ECS < 40:
"عملکرد اولیه امیدوارکننده است، اما شواهد برای نتیجه‌گیری قوی هنوز کافی نیست."


اگر FRS_30D <= FRS_90D - 7 و ECS_30D >= 40:
"عملکرد اخیر نسبت به روند میان‌مدت تضعیف شده است."


اگر همان افت ولی ECS_30D < 40:
"عملکرد اخیر ضعیف‌تر دیده می‌شود، اما شواهد کوتاه‌مدت هنوز محدود است."


اگر BenchmarkEdgeScore < 50:
"مدل در این بازه نسبت به benchmark اصلی برتری نشان نداده است."


============================================================
26. محدودیت‌های علمی
============================================================


FRF 2.0 تضمین سودآوری نیست.
FRS احتمال موفقیت معامله بعدی نیست.
FRS = 80 به معنی 80% احتمال درست بودن forecast بعدی نیست.


FRF فقط شواهد تاریخی forward/live ثبت‌شده را ارزیابی می‌کند.


Regime shift می‌تواند عملکرد آینده را تغییر دهد.
وابستگی بین predictionهای روزانه باعث می‌شود N خام همیشه برابر N مستقل آماری نباشد.
به همین دلیل ECS یک evidence heuristic مهندسی‌شده است، نه آزمون نهایی اثبات آماری.


برای پژوهش پیشرفته بعدی می‌توان:
- block bootstrap
- effective sample size
- Diebold-Mariano test
- McNemar test
- probabilistic skill scores
را اضافه کرد.


این موارد جزو FRF 2.0 V1 نیستند تا complexity کنترل شود.


============================================================
27. Definition of Done
============================================================


FRF 2.0 زمانی Done است که:


- migration جدید بدون data loss اجرا شود
- همه calculatorها unit test داشته باشند
- aggregate windows درست باشند
- scores versioned باشند
- integrity rules فعال باشند
- dashboard scoreها را نشان دهد
- daily HTML/email scoreها را نشان دهد
- v1 functionality regress نشود
- Cron روی PHP CLI 8.4 کار کند
- SQLite locking issue نداشته باشد
- all tests pass
- no trading capability added
- CODEX integration report تولید شود


============================================================
28. Master Instruction برای Codex
============================================================


Implement FRF 2.0 as an additive extension to the existing
AlpacaAIApp / AI Market Forecast Validation System codebase.


First locate and read the existing project specification and code.
The parent specification is:
"راهنمای پیاده‌سازی سیستم ارزیابی پیش‌بینی بازار با PHP و Codex — نسخه 1.0"
Google Drive File ID:
1WGcN21HZhf4d7Itycm2XoDBxrCodGxSitpu1tLNwNJs


This FRF 2.0 document is a companion specification, not a replacement.


Do not modify or weaken:
- immutable forecasts
- information cutoff rules
- market data validation
- outcome determinism
- security rules
- PHP 8.4 + SQLite + cPanel constraints
- no-trading V1 scope


Add FRF 2.0 using a new migration and new App\Reliability namespace.


Implement:
FPS, ECS, FRS,
cumulative/90D/30D/7D windows,
group scores,
trend detection,
confidence intervals,
integrity gating,
persistence,
cron aggregation,
dashboard integration,
daily HTML/email integration,
tests.


All FRF calculations must be deterministic PHP code.
Do not use an LLM to calculate, judge, repair, or interpret scores.
Human-readable interpretation must come from deterministic rules.


Never overwrite old score versions.
Never recalculate historical forecasts using future information.
Never fabricate missing data.
Unknown/incomplete state must fail closed.


Proceed through FRF-TASK-01 to FRF-TASK-13.
At each task:
- run tests
- fix failures
- report files changed
- report migrations affected
- report unresolved issues
- preserve backward compatibility


Final output:
- code
- migration
- tests
- cron
- dashboard changes
- email/report changes
- integration report
- all tests passing


============================================================
29. نتیجه نهایی طراحی
============================================================


FRF 2.0 سه سؤال را از هم جدا می‌کند:


FPS:
«خود مدل چقدر خوب پیش‌بینی می‌کند؟»


ECS:
«شواهد ما برای قضاوت درباره این عملکرد چقدر قوی است؟»


FRS:
«با در نظر گرفتن عملکرد و قدرت شواهد، اعتبار فعلی روش چقدر است؟»


این جداسازی مهم‌ترین تفاوت FRF 2.0 با نسخه اولیه است و باعث می‌شود AlpacaAIApp از یک dashboard ساده Accuracy به یک سیستم ارزیابی علمی‌تر، versioned و قابل‌اعتماد تبدیل شود.


امتیاز طراحی مهندسی پیشنهادی:
9.9 / 10


این امتیاز مربوط به کیفیت طراحی سیستم ارزیابی است، نه تضمین دقت پیش‌بینی یا سودآوری بازار.
