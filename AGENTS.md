# Project Instructions

The authoritative specifications are ordered as follows:
1. `docs/AI_MARKET_FORECAST_VALIDATION_SPEC.md`
2. `docs/AI_MARKET_FORECAST_FRF_2_0_SPEC.md` for reliability/evaluation aggregation
3. `docs/DAILY_HTML_REPORT_EMAIL_USER_PANEL_SPEC.md` for reports, email, and user panel
Implement the parent foundation first, then integrate FRF 2.0 before final release, then implement daily report/email/user panel. Keep each task internally consistent.
Trading and paper trading are disabled in V1. Never add order-placement code.
Never commit secrets or fabricate external data. Use fixtures in automated tests.
