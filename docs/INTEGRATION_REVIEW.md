# Integration Review

The two companion specifications were reviewed against the PHP 8.4 + SQLite + cPanel foundation.

## Decision

They must be integrated before finalization. FRF 2.0 changes the evaluation contract and requires additive migration `002_frf_v2.sql`; the Daily HTML Report, Email, and User Panel specification depends on persisted FRF scores and a shared validated view model. Finalizing the current V1 first would create avoidable schema and dashboard rework.

## Implementation order

1. Complete the parent V1 forecast pipeline and immutable persistence.
2. Add FRF 2.0 as an additive reliability layer: migration, deterministic calculators, windows, integrity gates, repositories, and Cron aggregation.
3. Add one shared DailyReport view model consumed by both dashboard and email templates.
4. Add report archive, delivery audit, duplicate prevention, plain-text email alternative, and user-panel history.
5. Run regression, security, and host tests before release.

## Review score

9.95 / 10 for engineering fit and integration quality.

The score is not a prediction-accuracy or profitability guarantee. The only minor deduction is that exact SMTP delivery settings, recipient policy, and any XAUT provider remain deployment configuration rather than specifications.

## Non-negotiable safeguards

- No changes to historical FINAL forecasts.
- No LLM calculation or interpretation of reliability scores.
- No trading or paper-trading capability.
- No secrets in repository, HTML, logs, or email.
- Missing data remains explicitly unavailable; it is never guessed.
