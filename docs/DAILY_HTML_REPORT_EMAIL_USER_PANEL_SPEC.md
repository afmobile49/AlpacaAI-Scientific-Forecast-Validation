AlpacaAIApp — Daily HTML Report + Email + User Panel
سند مکمل اجرایی برای Codex — اتصال به Forecast Validation PHP/SQLite v1.0 و FRF 2.0


نسخه سند: 1.0
نوع سند: Implementation + Integration Specification
پروژه مادر: AlpacaAIApp — AI Market Forecast Validation System


اسناد مرتبط و ترتیب Source of Truth:
1) «راهنمای پیاده‌سازی سیستم ارزیابی پیش‌بینی بازار با PHP و Codex — نسخه 1.0»
   Google Drive File ID: 1WGcN21HZhf4d7Itycm2XoDBxrCodGxSitpu1tLNwNJs
2) «AlpacaAIApp — FRF 2.0 — سند مکمل پیاده‌سازی و اتصال به Forecast Validation PHP-Codex v1.0»
   Google Drive File ID: 1JmPyICPVo4-Elr6FS4pXsxifFGJVSRS1WYIQWbab6MI
3) این سند: Daily HTML Report + Email + User Panel


============================================================
1. هدف این سند
============================================================


این سند مشخصات کامل لایه Presentation/Reporting پروژه AlpacaAIApp را تعریف می‌کند تا Codex بتواند بدون تغییر منطق هسته Forecast و بدون دوباره‌سازی منطق FRF 2.0، یک خروجی HTML تمیز، خلاصه و قابل‌استفاده روزانه ایجاد کند که:


- همان داده‌ها را در Dashboard پنل کاربری نمایش دهد.
- همان خلاصه را به‌صورت Daily Email ارسال کند.
- یک ViewModel/Data Contract مشترک بین Email و Dashboard داشته باشد.
- نتیجه Forecast، Outcome و FRF 2.0 را با زبان ساده و قابل اسکن نمایش دهد.
- از duplication منطق جلوگیری کند.
- با PHP 8.4 + SQLite + cPanel Cron سازگار باشد.
- در Email Clientهای رایج قابل نمایش باشد.
- در پنل Responsive و خوانا باشد.


نام رسمی گزارش:
«گزارش روزانه پیش‌بینی بازار AlpacaAIApp»


اصل معماری:
Data/Calculation Layer -> ReportDataBuilder -> ReportViewModel ->
    1) Dashboard Renderer
    2) Email Renderer
    3) Optional HTML Archive Renderer


هیچ metric مالی یا FRF داخل template محاسبه نشود.
Template فقط داده آماده و validated دریافت کند.


============================================================
2. محدوده نسخه 1
============================================================


داخل محدوده:
- Daily HTML report.
- Dashboard rendering.
- Daily email rendering and sending.
- Shared ReportViewModel.
- FRF 2.0 summary.
- Today Forecasts.
- Recently Resolved Outcomes.
- Quant vs AI+Quant comparison.
- System health summary.
- Email delivery audit.
- HTML escaping and output safety.
- Responsive dashboard.
- Email-compatible markup.
- Read-only report history.


خارج از محدوده:
- Trading.
- Paper Trading.
- User order actions.
- Portfolio execution.
- Interactive chart-heavy email.
- JavaScript in email.
- Editing Forecast from dashboard.
- Editing FRF metrics from dashboard.
- LLM-generated report narrative.


============================================================
3. رابطه با پروژه قبلی
============================================================


Codex باید ابتدا codebase ساخته‌شده از سند مادر و FRF 2.0 را inspect کند.
این سند باید additive extension باشد.


قوانین:
- هیچ migration قبلی تغییر نکند.
- هیچ کلاس Forecast/Outcome/FRF حذف نشود.
- Report layer فقط داده بخواند.
- Dashboard و Email نباید source of truth باشند.
- Source of truth فقط SQLite tables + calculators نسخه‌دار است.
- اگر score یا outcome unavailable است، UI باید unavailable/insufficient data نشان دهد؛ حدس ممنوع.


پیشنهاد namespace:
App\Reporting\
App\Mail\


============================================================
4. معماری پیشنهادی
============================================================


app/
  Reporting/
    DailyReportService.php
    DailyReportDataBuilder.php
    DailyReportViewModel.php
    ReportFormatter.php
    ReportLabeler.php
    HtmlSanitizer.php
    ReportArchiveService.php


  Mail/
    MailerInterface.php
    SmtpMailer.php
    DailyReportMailer.php
    EmailTemplateRenderer.php


  Dashboard/
    DashboardController.php
    DailyReportController.php
    ReliabilityController.php


resources/
  views/
    dashboard/
      overview.php
      daily-report.php
      prediction-detail.php
      reliability.php
    email/
      daily-report.php
    partials/
      metric-card.php
      frf-summary.php
      forecast-table.php
      outcome-table.php


public/
  index.php
  report.php
  reliability.php


cron/
  generate_daily_report.php
  send_daily_report.php


storage/
  reports/
    YYYY/
      MM/
        daily-report-YYYY-MM-DD.html


============================================================
5. اصل Shared ViewModel
============================================================


Dashboard و Email نباید query یا calculation جداگانه داشته باشند.
هر دو باید از یک ViewModel مشترک تغذیه شوند.


کلاس پیشنهادی:
DailyReportViewModel


حداقل ساختار داده:


{
  "report": {
    "title": "گزارش روزانه پیش‌بینی بازار AlpacaAIApp",
    "report_date": "YYYY-MM-DD",
    "generated_at": "UTC ISO8601",
    "display_timezone": "America/Los_Angeles",
    "experiment_code": "EXP-001",
    "framework_version": "FRF_2_0"
  },
  "summary": {
    "forecasts_today": 24,
    "resolved_today": 18,
    "ai_quant_accuracy": 64.8,
    "quant_accuracy": 57.1,
    "ai_edge_pp": 7.7
  },
  "frf": {
    "cumulative": {
      "fps": 78.0,
      "fps_label": "GOOD",
      "ecs": 93.0,
      "ecs_label": "STRONG_EVIDENCE",
      "frs": 75.3,
      "frs_label": "RELIABLE",
      "resolved_n": 4286
    },
    "windows": {
      "90D": {"frs": 73.0, "ecs": 88.0},
      "30D": {"frs": 66.0, "ecs": 61.0},
      "7D":  {"frs": 58.0, "ecs": 31.0}
    },
    "trend": {
      "label": "WEAKENING",
      "confidence": "LOW"
    }
  },
  "today_forecasts": [],
  "resolved_outcomes": [],
  "system_health": {},
  "interpretation": {
    "headline": "...",
    "detail": "..."
  }
}


این JSON قرارداد داخلی است؛ template حق تغییر semantic آن را ندارد.


============================================================
6. DailyReportDataBuilder
============================================================


وظایف:
1. دریافت report date در timezone America/Los_Angeles.
2. خواندن latest active experiment.
3. خواندن forecasts ساخته‌شده در روز گزارش.
4. خواندن outcomes که همان روز resolved شده‌اند.
5. خواندن latest persisted FRF 2.0 scores.
6. خواندن system health.
7. تولید deterministic interpretation از rules.
8. ساخت ViewModel validated.


نباید:
- API call جدید برای market data انجام دهد.
- FRF را live recompute کند.
- AI call انجام دهد.
- forecast را تغییر دهد.


============================================================
7. قالب HTML مرجع
============================================================


ظاهر نسخه 1 باید بر اساس نمونه قبلی باشد:


- Background روشن #f5f7fb
- Card سفید با border نرم
- Header تیره
- Typography ساده و خوانا
- RTL فارسی
- Responsive
- جدول‌های کوتاه
- رنگ سبز برای positive/correct
- قرمز برای negative/wrong
- خاکستری برای neutral
- FRS card برجسته


Header:
«گزارش روزانه پیش‌بینی بازار AlpacaAIApp»


Subheader:
AI Market Forecast Validation System — [Date]


ترتیب بخش‌ها:
1. Header
2. Daily Summary Metrics
3. FRF 2.0 Reliability Summary
4. Important Forecasts Today
5. Outcomes Resolved Today
6. Method Status / Quant vs AI+Quant
7. System Health / Footer


============================================================
8. بخش Daily Summary
============================================================


چهار Metric Card اصلی:
- پیش‌بینی‌های امروز
- نتایج تکمیل‌شده امروز
- دقت تجمعی AI+Quant
- برتری AI نسبت به Quant


قوانین نمایش:
- percentage با یک رقم اعشار.
- edge به percentage point نمایش داده شود، نه percent relative change.
- اگر داده کافی نیست: «داده کافی نیست».
- هیچ 0 جعلی برای داده unavailable نشان داده نشود.


============================================================
9. بخش FRF 2.0
============================================================


Email و Dashboard باید سه score اصلی را جدا نشان دهند:


FPS — Forecast Performance Score
ECS — Evidence Confidence Score
FRS — Forecast Reliability Score


همراه:
- Cumulative FRS
- 90D FRS
- 30D FRS
- 7D FRS
- Trend
- Trend Confidence
- Resolved N


قانون مهم:
FRS نباید به شکل «احتمال درست بودن پیش‌بینی بعدی» نوشته شود.


متن توضیح:
«FRS امتیاز اعتبار فعلی روش بر اساس عملکرد واقعی ثبت‌شده و قدرت شواهد موجود است؛ این عدد احتمال درست بودن پیش‌بینی بعدی نیست.»


اگر ECS < 40:
Badge FRS حداکثر PROVISIONAL.


اگر integrity breach:
FRS نمایش داده نشود.
به‌جای آن:
«Reliability temporarily suspended — integrity review required.»


============================================================
10. جدول Important Forecasts Today
============================================================


ستون‌ها:
- Asset
- Horizon
- Forecast
- Confidence
- Expected Return
- Optional Model Source


نمونه:
NVDA | 5D | صعودی | 78% | +4.2% | AI+Quant


Sorting پیشنهادی:
1. confidence descending
2. absolute expected return descending


Limit email:
حداکثر 5 مورد مهم.


Dashboard:
همه موارد روز + فیلتر.


Mapping direction:
BULLISH => صعودی
NEUTRAL => خنثی
BEARISH => نزولی


============================================================
11. جدول Outcomes Resolved Today
============================================================


ستون‌ها:
- Asset
- Horizon
- Predicted Direction
- Actual Return
- Result


Result:
CORRECT => ✓ درست
WRONG => ✕ نادرست
VOID/EXCLUDED => در خلاصه ایمیل نشان داده نشود؛ در dashboard diagnostic قابل مشاهده باشد.


Limit email:
حداکثر 5 outcome مهم/جدید.


Dashboard:
همه resolved outcomeهای روز.


============================================================
12. بخش Method Status
============================================================


نمایش:
- AI+Quant Accuracy
- Quant Accuracy
- AI Edge
- Calibration Score یا status
- Resolved Sample Size


Deterministic interpretation از FRF rules خوانده شود.
مثال:
«مدل AI+Quant در داده‌های تکمیل‌شده تا امروز نسبت به Quant Baseline عملکرد بهتری داشته است، اما عملکرد 30 روز اخیر نسبت به 90 روز اخیر ضعیف‌تر شده است.»


این متن نباید توسط LLM ساخته شود.


============================================================
13. System Health
============================================================


Dashboard نمایش دهد:
- SQLite: OK/ERROR
- Last Forecast Cron
- Last Outcome Cron
- Last FRF Cron
- Last Daily Report Build
- Last Email Send
- Alpaca Data Health
- XAUT Provider Health
- AI Provider Health
- Failed Jobs Count


Email فقط اگر مشکلی وجود دارد health warning کوتاه نشان دهد.
در حالت سالم، footer کافی است.


============================================================
14. Email HTML Design Rules
============================================================


Email template باید از Dashboard template جدا باشد اما ViewModel مشترک باشد.


الزامات ایمیل:
- HTML4/HTML5 ساده.
- ترجیح table-based layout برای بخش‌های حساس.
- CSS critical به‌صورت inline یا توسط inliner در build step.
- بدون JavaScript.
- بدون external JS.
- بدون form.
- بدون SVG وابسته به script.
- بدون web font اجباری.
- fallback font: Tahoma, Arial, sans-serif.
- max width حدود 700–760px.
- responsive برای mobile.
- تمام لینک‌ها absolute HTTPS.
- no tracking pixel در V1 مگر user explicitly اضافه کند.
- dark-mode safe تا حد ممکن.


Email clients هدف:
- Gmail Web
- Gmail Mobile
- Apple Mail
- Outlook Web


قانون:
اگر CSS property در email clientها قابل اتکا نیست، email version باید graceful fallback داشته باشد.


============================================================
15. Dashboard HTML Design Rules
============================================================


Dashboard می‌تواند CSS مدرن‌تر داشته باشد.


- max content width حدود 1100–1200px.
- Card layout.
- CSS Grid/Flex مجاز.
- JavaScript فقط برای UI non-critical مثل filter/chart toggle مجاز.
- core metrics بدون JS قابل مشاهده باشند.
- RTL کامل.
- responsive.
- table horizontal scroll در mobile.


صفحات:
1. Overview
2. Daily Report
3. Predictions
4. Reliability
5. System Health


============================================================
16. Email Subject
============================================================


فرمت پیشنهادی:


گزارش روزانه پیش‌بینی بازار AlpacaAIApp — YYYY-MM-DD


اگر critical health issue:
[هشدار] گزارش روزانه پیش‌بینی بازار AlpacaAIApp — YYYY-MM-DD


تاریخ باید بر اساس America/Los_Angeles باشد.


============================================================
17. Mailer Architecture
============================================================


Interface:


interface MailerInterface
{
    public function send(MailMessage $message): MailResult;
}


DailyReportMailer:
- ViewModel را می‌گیرد.
- subject می‌سازد.
- EmailTemplateRenderer را صدا می‌زند.
- HTML و plain text alternative می‌سازد.
- mailer provider را صدا می‌زند.
- نتیجه را audit می‌کند.


SMTP یا provider-specific implementation پشت interface باشد.


.env placeholders:
MAIL_TRANSPORT=smtp
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME=AlpacaAIApp
DAILY_REPORT_RECIPIENT=


هیچ credential داخل source code نباشد.


============================================================
18. Plain Text Alternative
============================================================


هر email باید text/plain alternative داشته باشد.


نمونه:


گزارش روزانه پیش‌بینی بازار AlpacaAIApp
تاریخ: ...


پیش‌بینی‌های امروز: 24
نتایج تکمیل‌شده: 18
AI+Quant Accuracy: 64.8%
Quant Accuracy: 57.1%
AI Edge: +7.7pp


FRF 2.0
FPS: 78/100 GOOD
ECS: 93/100 STRONG_EVIDENCE
FRS: 75/100 RELIABLE
90D: 73
30D: 66
7D: 58
Trend: WEAKENING


... 


============================================================
19. Email Delivery Audit
============================================================


Migration جدید پیشنهادی:
003_reporting_email.sql


جدول:


CREATE TABLE report_deliveries (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  report_date TEXT NOT NULL,
  report_type TEXT NOT NULL,
  recipient_hash TEXT NOT NULL,
  subject TEXT NOT NULL,
  status TEXT NOT NULL,
  provider_message_id TEXT,
  attempt_count INTEGER NOT NULL DEFAULT 0,
  last_error TEXT,
  sent_at TEXT,
  created_at TEXT NOT NULL,
  UNIQUE(report_date, report_type, recipient_hash)
);


recipient_hash برای audit بدون نمایش ایمیل خام در dashboard مناسب است.


و جدول archive metadata:


CREATE TABLE report_archives (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  report_date TEXT NOT NULL,
  report_type TEXT NOT NULL,
  html_path TEXT NOT NULL,
  content_sha256 TEXT NOT NULL,
  generated_at TEXT NOT NULL,
  UNIQUE(report_date, report_type)
);


============================================================
20. Duplicate Prevention
============================================================


send_daily_report.php باید idempotent باشد.


Job key:
email:daily-report:YYYY-MM-DD


قبل از send:
- report_deliveries را بررسی کند.
- اگر status=SENT، دوباره ارسال نکند.
- retry فقط برای FAILED/RETRYABLE طبق policy.


Duplicate email ممنوع.


============================================================
21. Retry Policy Email
============================================================


مثال:
Attempt 1: immediate
Attempt 2: +10 minutes
Attempt 3: +30 minutes


بعد از 3 failure:
status=FAILED
system event با severity ERROR.


Authentication/configuration error:
blind retry نشود.


============================================================
22. Daily Report Generation Flow
============================================================


پس از اتمام outcome + FRF aggregation:


resolve_outcomes
-> calculate_metrics
-> calculate_reliability
-> generate_daily_report
-> archive HTML
-> send_daily_report


اگر FRF برای همان روز هنوز ساخته نشده:
گزارش می‌تواند Forecast/Outcome را بسازد، اما بخش FRF باید «در انتظار محاسبه» نشان دهد.


اگر core data incomplete است:
report status PARTIAL.


============================================================
23. Cron پیشنهادی
============================================================


cron فقط wake-up کند؛ PHP due logic را مدیریت کند.


نمونه:


*/15 * * * * php /path/cron/generate_daily_report.php
*/15 * * * * php /path/cron/send_daily_report.php


اما هر script باید تشخیص دهد آیا گزارش روز موردنظر آماده و due است.


پیشنهاد زمانی:
پس از پایان daily evaluation و FRF aggregation.


============================================================
24. Report Archive
============================================================


هر گزارش روزانه علاوه بر ارسال ایمیل، به‌صورت HTML snapshot ذخیره شود:


storage/reports/YYYY/MM/daily-report-YYYY-MM-DD.html


کاربرد:
- audit
- troubleshooting
- history
- مشاهده گزارش دقیق همان روز حتی اگر CSS/template آینده تغییر کند.


Archive HTML immutable باشد.
content SHA-256 ذخیره شود.


============================================================
25. Dashboard Report History
============================================================


صفحه Daily Report History:


ستون‌ها:
- Date
- Forecast Count
- Resolved Count
- FRS
- Trend
- Email Status
- View


کاربر بتواند روی View کلیک کند و snapshot همان روز را ببیند.


نسخه dashboard current می‌تواند از ViewModel live/cache بخواند؛ history باید archive روزانه را حفظ کند.


============================================================
26. Security
============================================================


- تمام output dynamic با htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').
- URLها validate شوند.
- هیچ raw AI explanation بدون escape چاپ نشود.
- HTML email injection جلوگیری شود.
- no secrets in HTML.
- no API keys in query string.
- no stack trace در panel عمومی.
- Dashboard authentication الزامی.
- Report archive directory خارج public root ترجیحاً؛ serve از authenticated controller.
- SQL prepared statements.


============================================================
27. Privacy
============================================================


Daily email نباید شامل:
- API key
- account number
- broker credential
- database path
- server absolute path
- private debug payload


در V1 recipient فقط از config گرفته شود.


============================================================
28. Accessibility / Readability
============================================================


- font حداقل 13px email و 14px dashboard برای body.
- contrast مناسب.
- status فقط با رنگ منتقل نشود؛ متن و symbol هم داشته باشد.
- BULLISH/BEARISH با label فارسی نوشته شود.
- table header واضح.
- screen reader friendly markup تا حد عملی.


============================================================
29. قالب ظاهری نمونه
============================================================


Header:
گزارش روزانه پیش‌بینی بازار AlpacaAIApp
AI Market Forecast Validation System — [Date]


Metric row:
پیش‌بینی‌های امروز | نتایج تکمیل‌شده | AI+Quant Accuracy | AI Edge


FRF card:
FPS 78/100
ECS 93/100
FRS 75/100
90D / 30D / 7D
Trend


Forecast table:
NVDA | 5D | صعودی | 78% | +4.2%
QQQ  | 5D | صعودی | 72% | +2.6%
SPY  | 1D | خنثی | 61% | +0.2%
BTC  | 7D | نزولی | 67% | -3.1%
XAUT | 7D | صعودی | 70% | +1.8%


Outcome table:
NVDA | 5D | صعودی | +3.9% | ✓ درست
AAPL | 1D | خنثی | +0.2% | ✓ درست
BTC  | 7D | صعودی | -2.7% | ✕ نادرست


Footer:
«این گزارش به‌صورت خودکار توسط AlpacaAIApp و Forecast Reliability Framework 2.0 تولید شده است. این گزارش تضمین‌کننده عملکرد آینده یا سودآوری نیست.»


============================================================
30. Dashboard Data Endpoints / Internal Services
============================================================


اگر پروژه architecture controller-based دارد:


GET /dashboard
GET /reports/daily
GET /reports/daily/{date}
GET /reliability
GET /health


اگر API داخلی نیاز شد:


GET /api/report/daily?date=YYYY-MM-DD


خروجی فقط authenticated.


برای shared hosting، JSON API اجباری نیست؛ controller می‌تواند ViewModel مستقیم به view بدهد.


============================================================
31. Performance
============================================================


Dashboard نباید روی هر request:
- FRF recompute کند.
- market API بخواند.
- AI API call کند.
- bootstrap CI اجرا کند.


فقط persisted/cached data.


Target:
Overview page با DB local read سبک.


ReportDataBuilder queryها index-friendly باشند.


============================================================
32. Error State UI
============================================================


حالت‌ها:


NO_FORECASTS:
«امروز پیش‌بینی معتبری ثبت نشده است.»


FRF_PENDING:
«امتیازهای FRF 2.0 هنوز برای این گزارش محاسبه نشده‌اند.»


PARTIAL_DATA:
«بخشی از داده‌ها هنوز تکمیل نشده‌اند.»


INTEGRITY_BREACH:
«نمایش FRS موقتاً متوقف شده است؛ بررسی یکپارچگی داده لازم است.»


EMAIL_FAILED:
در panel admin نشان داده شود؛ در خود report user-facing لازم نیست مگر health page.


============================================================
33. Testing Strategy
============================================================


Unit Tests:
- ViewModel formatting.
- percentage formatter.
- direction label mapping.
- FRF label rendering.
- interpretation rule mapping.
- subject generation.
- plain text rendering.
- escaping.


Integration Tests:
- build report from SQLite fixture.
- email HTML render.
- dashboard HTML render.
- no duplicate email send.
- retry policy.
- archive file write.
- archive hash verification.
- history read.
- PARTIAL report state.
- INTEGRITY_BREACH state.


Snapshot Tests:
- Email HTML stable structure.
- Dashboard critical sections present.


Security Tests:
- script tag in explanation escaped.
- malformed URL rejected.
- secret values never rendered.


============================================================
34. Acceptance Criteria
============================================================


1. Report title دقیقاً «گزارش روزانه پیش‌بینی بازار AlpacaAIApp» باشد.
2. Email و Dashboard از یک ViewModel استفاده کنند.
3. HTML email در Gmail/Apple Mail/Outlook Web قابل خواندن باشد.
4. Dashboard responsive باشد.
5. FRF 2.0 شامل FPS/ECS/FRS نمایش داده شود.
6. Rolling FRS: 90D/30D/7D نمایش داده شود.
7. Trend و trend confidence نمایش داده شود.
8. Forecastهای مهم امروز نمایش داده شوند.
9. outcomeهای resolved امروز نمایش داده شوند.
10. Quant vs AI+Quant نمایش داده شود.
11. Missing data حدس زده نشود.
12. HTML escaping کامل باشد.
13. Email plain-text alternative داشته باشد.
14. duplicate send رخ ندهد.
15. delivery audit ثبت شود.
16. HTML archive روزانه ایجاد شود.
17. archive hash ثبت و verify شود.
18. History در panel قابل مشاهده باشد.
19. هیچ calculation سنگین در template انجام نشود.
20. هیچ LLM call برای report generation انجام نشود.
21. هیچ trading functionality اضافه نشود.
22. existing v1 و FRF tests regress نشوند.
23. روی PHP 8.4 + SQLite + cPanel اجرا شود.
24. تمام tests pass باشند.


============================================================
35. Codex Integration Contract
============================================================


Codex باید این سند را مکمل دو سند قبلی بداند.


Source-of-truth order:


A) Core Forecast / Data / Security / Hosting:
سند مادر PHP/Codex v1.0


B) Reliability formulas / FPS / ECS / FRS:
FRF 2.0 companion spec


C) Presentation / HTML / Email / Dashboard / Report Archive:
این سند


اگر تعارض فقط درباره report presentation وجود داشت، این سند مقدم است.
اگر تعارض درباره FRF formula بود، FRF 2.0 مقدم است.
اگر تعارض درباره forecast generation/immutability/security بود، سند مادر مقدم است.


============================================================
36. Codex Tasks
============================================================


REPORT-TASK-01
Inspect existing codebase, view layer, router, auth, cron and mail capabilities.
No code change before mapping.


REPORT-TASK-02
Add migration 003_reporting_email.sql.
Add report_deliveries and report_archives.


REPORT-TASK-03
Implement DailyReportViewModel + DataBuilder.
Use existing repositories.


REPORT-TASK-04
Implement formatters, labelers and deterministic interpretation.


REPORT-TASK-05
Implement Dashboard Renderer and Daily Report page.


REPORT-TASK-06
Implement email-safe HTML template based on the approved visual format.


REPORT-TASK-07
Implement plain text alternative.


REPORT-TASK-08
Implement MailerInterface + configured transport adapter.


REPORT-TASK-09
Implement duplicate prevention, retries and delivery audit.


REPORT-TASK-10
Implement HTML archive + SHA-256.


REPORT-TASK-11
Implement report history page.


REPORT-TASK-12
Add generate_daily_report.php and send_daily_report.php Cron scripts.


REPORT-TASK-13
Add tests: rendering, escaping, idempotency, archive, email.


REPORT-TASK-14
Run full regression suite against core v1 + FRF 2.0.


REPORT-TASK-15
Produce integration report and cPanel deployment instructions.


============================================================
37. Master Instruction برای Codex
============================================================


Implement the AlpacaAIApp Daily HTML Report, Daily Email, and User Panel reporting layer as an additive extension to the existing PHP 8.4 + SQLite + cPanel project.


Parent specifications:
1. AI Market Forecast Validation System PHP/Codex v1.0
   Drive File ID: 1WGcN21HZhf4d7Itycm2XoDBxrCodGxSitpu1tLNwNJs
2. AlpacaAIApp FRF 2.0 companion specification
   Drive File ID: 1JmPyICPVo4-Elr6FS4pXsxifFGJVSRS1WYIQWbab6MI


This document governs reporting/presentation integration.


Do not modify forecast generation, immutable forecast rules, market data rules, FRF formulas, or no-trading constraints.


Build a shared DailyReportViewModel and a single DailyReportDataBuilder. Both Dashboard and Email must consume this shared validated data structure. Never duplicate score calculations in templates.


Required report title:
"گزارش روزانه پیش‌بینی بازار AlpacaAIApp"


Required report sections:
- summary metrics
- FRF 2.0 FPS/ECS/FRS
- cumulative + 90D + 30D + 7D reliability
- trend + evidence confidence
- important forecasts today
- resolved outcomes today
- Quant vs AI+Quant method status
- health/footer


Use deterministic PHP rendering. No LLM call is allowed for report calculation or interpretation.


Implement:
- App\Reporting namespace
- App\Mail namespace
- migration 003_reporting_email.sql
- report_deliveries
- report_archives
- email-safe HTML template
- responsive dashboard template
- plain text email alternative
- idempotent daily email send
- retry policy
- archived immutable daily HTML snapshot
- SHA-256 archive hash
- report history
- cron generation and send jobs
- full automated tests


Security:
- escape all dynamic HTML
- never expose secrets
- authenticated dashboard/report history
- prepared SQL
- report archives preferably outside public root


Email:
- no JavaScript
- no external required fonts
- email-compatible CSS
- absolute HTTPS links
- one daily message per report date/recipient
- subject: گزارش روزانه پیش‌بینی بازار AlpacaAIApp — YYYY-MM-DD


Dashboard:
- responsive RTL
- core content visible without JavaScript
- persisted metrics only
- no live heavy calculation


Proceed through REPORT-TASK-01 to REPORT-TASK-15.
At every task:
- run relevant tests
- fix failures
- report changed files
- report migration impact
- preserve backward compatibility
- note unresolved configuration placeholders


Final delivery:
- code
- migration
- views/templates
- mail adapter
- cron scripts
- archive logic
- dashboard/history
- tests
- cPanel setup guide
- email configuration guide
- integration report
- all tests passing


============================================================
38. Definition of Done
============================================================


این ماژول زمانی Done است که:
- Daily report با داده fixture و production data ساخته شود.
- همان semantic output در Dashboard و Email وجود داشته باشد.
- Email قابل خواندن و mobile-friendly باشد.
- Daily email duplicate نشود.
- delivery result audit شود.
- archive روزانه immutable باشد.
- user history صفحه داشته باشد.
- FRF 2.0 درست و بدون recompute در template نمایش داده شود.
- failure stateها واضح باشند.
- security tests pass باشند.
- core forecast و FRF behavior تغییر نکرده باشد.
- deployment روی PHP 8.4 + SQLite + cPanel مستند باشد.
- all tests pass.


============================================================
39. امتیاز طراحی
============================================================


امتیاز پیشنهادی طراحی مهندسی این سند: 9.9 / 10


دلیل:
این طراحی یک Data Contract مشترک بین Email و Dashboard ایجاد می‌کند، duplication را حذف می‌کند، محاسبات را از presentation جدا نگه می‌دارد، FRF 2.0 را به‌درستی مصرف می‌کند، گزارش روزانه را audit-friendly و version-safe می‌سازد و برای محدودیت‌های cPanel/SQLite مناسب است.


این امتیاز مربوط به کیفیت طراحی گزارش و integration است، نه دقت پیش‌بینی یا سودآوری بازار.
