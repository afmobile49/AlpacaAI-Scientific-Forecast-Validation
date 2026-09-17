# Market Cycle Scheduling Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:executing-plans` task-by-task with test-first changes.

**Goal:** Make official stock forecasts run only after a completed US session while running a separate daily crypto cycle, followed by the corresponding report and email.

**Architecture:** `MarketCalendarService` becomes the sole authority for stock-session close times. `run_forecast.php` accepts an explicit cycle (`stock` or `crypto`), selects only compatible assets, and uses a cycle-specific immutable run key. A versioned cron manifest provides the dependency order for forecast, reporting, reference capture, outcome resolution, evaluation, benchmark, and FRF jobs.

**Global constraints:** preserve historical FINAL records; do not retry or rewrite failed historical runs; no backfill; shared Quant/AI cutoff and snapshots; XAUT fail-closed; `ENABLE_TRADING=false`; `ENABLE_PAPER_TRADING=false`.

### Task 1: Calendar-backed completed-session guard

- [ ] Add tests proving 14:05 ET is rejected, 16:20 ET is accepted, and an early-close session is accepted after 13:00 ET.
- [ ] Replace hard-coded stock-close logic in `CompletedMarketBarGuard` with `MarketCalendarService`.
- [ ] Run the focused tests and the full PHPUnit suite.

### Task 2: Explicit cycle selection and immutable run identity

- [ ] Add runner-level tests for stock-only and crypto-only asset selection and distinct run keys.
- [ ] Add a small cycle value object/parser; reject invalid cycles before any provider request.
- [ ] Change `run_forecast.php` to consume the cycle, use `stock-YYYY-MM-DD`/`crypto-YYYY-MM-DD`, and retain failed runs as audit evidence.
- [ ] Run syntax, focused, and full tests.

### Task 3: Cron manifest and preflight

- [ ] Add a testable `CronSchedulePlan` that derives session-safe job times from the configured `America/New_York` market timezone.
- [ ] Add a read-only preflight script validating ordering, timezone, cycle coverage, and forbidden trading flags.
- [ ] Document atomic crontab deployment/rollback without applying it during tests.
- [ ] Run focused and full tests.

### Task 4: Email fixture warning and cycle-aware report triggers

- [ ] Add a failing renderer test for sparse retry payloads.
- [ ] Make the text renderer safely render the required retry fields without warnings.
- [ ] Ensure cron manifest sends each cycle report only after its forecast job completes successfully.
- [ ] Run full PHPUnit with warnings displayed.

### Task 5: Production-safe verification and deployment

- [ ] Run lint on every changed PHP file and the full local test suite.
- [ ] Transfer only reviewed changed files to private/public host paths; do not copy `.env` or SQLite database.
- [ ] Run remote lint, migration/schema verification, schedule preflight, and pre-activation safe smoke.
- [ ] Back up the existing crontab, install the reviewed schedule atomically, and verify it read-only.
- [ ] Do not manually trigger a live forecast during an incomplete stock session.
