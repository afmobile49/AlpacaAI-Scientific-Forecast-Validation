# AlpacaAI — Scientific Forecast Validation

A PHP 8 and SQLite application for scientifically valid, reproducible **forward validation** of paired Quant and AI market forecasts.

It does not place trades. Instead, it records what each method predicted at a fixed point in time, captures the market evidence used, and later compares the immutable prediction with an objectively resolved market outcome.

> Research and validation software only. This project is not investment advice and makes no claim of profitability.

## What makes the validation credible

- **Paired Quant and AI forecasts** — each eligible asset/horizon cycle produces independent final predictions that are linked by a `forecast_pair_id`.
- **Shared evidence** — both sides of a pair use the same central cutoff, market snapshot, and feature snapshot hash.
- **Immutable lifecycle** — finalized forecasts retain their methodology and price-rule versions; historical semantics are never silently rewritten.
- **Deterministic outcomes** — versioned stock and crypto reference/outcome price rules resolve real returns only after their targets mature.
- **Fair comparison** — pre-AI records are excluded from official paired statistics; AI Edge is calculated only from shared paired observations.
- **Audit-ready reporting** — evaluation, benchmark, Forecast Reliability Framework (FRF 2.0), HTML reports, email reports, and a read-only panel are derived from stored evidence.

## Architecture

```text
Alpaca market data
        ↓
Authoritative cutoff → market snapshot → feature snapshot (hash)
        ↓                              ↓
   Quant baseline                   AI prediction
        ↓                              ↓
        └──── immutable FINAL predictions ────┘
                         ↓
                  forecast pair
                         ↓
      reference price → target → outcome price
                         ↓
         evaluation → benchmarks → FRF 2.0 → reports
```

## Core capabilities

- Quant baseline forecasting with versioned methodology.
- AI forecasting isolated from Quant failures and guarded by production-readiness checks.
- Equity trading-session handling, including early closes and missing-session behavior.
- Crypto target-time resolution using deterministic bars and explicit tolerances.
- Outcome evaluation using a neutral band and forward-only target lifecycle.
- Daily HTML/email reports and a public read-only reports panel.
- Retry, rate-limit, pagination, and fail-closed handling for market-data collection.

## Safety invariants

- `ENABLE_TRADING=false`
- `ENABLE_PAPER_TRADING=false`
- XAUT data remains fail-closed when a supported authoritative source is unavailable.
- Credentials, SQLite databases, logs, caches, backups, and generated vendor packages are excluded from version control.

## Quick start

```bash
composer install
copy config\\.env.example .env
# Configure non-production API credentials in .env
php vendor/bin/phpunit
```

Never commit `.env`, API keys, databases, or generated report data. The included configuration example contains placeholders only.

## Project layout

```text
app/          Domain services, orchestration, evaluation, reporting
config/       Environment configuration example
database/     Schema and migrations
public/       Read-only report panel entry points
scripts/      Cron-safe operational entry points
tests/        Unit, integration, and offline end-to-end tests
```

## Technology

PHP 8 · SQLite · PHPUnit · Alpaca Market Data API · OpenAI-compatible AI provider integration

## Operational model

The application is designed to run scheduled forecast, capture, resolution, evaluation, aggregation, reporting, and email jobs. Natural target maturity is required before real predictive performance can be assessed; the system deliberately does not fabricate outcomes or backfill historical AI results.
