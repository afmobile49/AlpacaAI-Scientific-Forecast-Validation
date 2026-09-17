-- Fresh installations receive nullable expected_return_pct directly from 001_initial.sql.
-- Existing production databases already applied the historical rebuild under this version;
-- this intentionally immutable marker prevents a second destructive table rewrite.
SELECT 1;
