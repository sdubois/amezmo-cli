# Changelog

## Unreleased

## 0.1.0-beta.2 - 2026-11-08

- Added `cron list|get|create|update` for the API's cron endpoints. Scripts can be passed with `--command` or read from a file (or stdin with `-`) with `--command-file`. Cron entries can only be deleted from the dashboard.
- API errors now show the API's message, per-parameter validation errors, `code`, and `request_id`, following the 2026-09-10 error format. 404 responses no longer hide the API's message, and deployment upload errors no longer show only the HTTP status.
- `deployments create` now checks the documented 512MB archive limit and that `--repo-owner` and `--repo-name` are given together before uploading.

## 0.1.0-beta.1 - 2026-08-06

- Added the initial unofficial community CLI vertical slice.
