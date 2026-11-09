# Amezmo API endpoint inventory

Source: [official Amezmo API documentation](https://www.amezmo.com/docs/api), inspected 2026-11-08. The documented base URL is `https://api.amezmo.com/v1`; requests require a Bearer token and return JSON.

| Resource | Operation | Method | Path | Parameters/body | Response | Auth | Safety | Documentation / notes |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Accounts | Get current user | GET | `/me/` | none | 200 object: `team_context`, `instance_limit`, `email` | yes | read-only | [page](https://www.amezmo.com/docs/api/accounts/get-the-current-user) |
| Instances | List instances | GET | `/instances` | optional `instance_type` query | 200 array of instance objects | yes | read-only | [page](https://www.amezmo.com/docs/api/instances/list-instances) |
| Instances | Get instance | GET | `/instances/{instance_id}` | `instance_id` URI | 200 instance object | yes | read-only | [page](https://www.amezmo.com/docs/api/instances/get-instance) |
| Instances | List instance types | GET | `/instance-types` | none | 200 array of `{id,name,hourly_price,monthly_price}` | yes | read-only | [page](https://www.amezmo.com/docs/api/instances/list-instance-types) |
| Instances | Create instance | POST | `/instances` | required `runtime`, `instance_type`, `region`; optional name/domain/php/mysql/app_type fields | 201 instance object | yes | mutating + confirmation | [page](https://www.amezmo.com/docs/api/instances/create-instance) |
| Instances | Terminate instance | DELETE | `/instances/{instance_id}` | `instance_id` URI | 200 instance object; state becomes deleting | yes | destructive + confirmation | [page](https://www.amezmo.com/docs/api/instances/terminate-instance) |
| Environments | List | GET | `/instances/{instance_id}/environments` | `instance_id` URI | 200 array of environment objects | yes | read-only | [page](https://www.amezmo.com/docs/api/environments/list-environments) |
| Environments | Get | GET | `/instances/{instance_id}/environments/{name}` | `instance_id`, `name` URI | 200 environment object | yes | read-only | [page](https://www.amezmo.com/docs/api/environments/get-environment) |
| Environments | Update | PATCH | `/instances/{instance_id}/environments/{name}` | optional `auto_deploy_tag_patterns`, `auto_deploy_branch_patterns`, `newrelic_license_key`, `ssh_enabled`, `trusted_ssh_ips` | 200 environment object | yes | mutating | [page](https://www.amezmo.com/docs/api/environments/update-environment) |
| Deployments | Get | GET | `/instances/{instance_id}/deployments/{deployment_id}` | URI IDs | 200 `{id,status}` | yes | read-only | [page](https://www.amezmo.com/docs/api/deployments/get) |
| Deployments | Create | POST | `/instances/{instance_id}/deployments` | multipart: `environment`, `archive`, optional repo metadata | 201 `{id,status}` | yes | mutating | [page](https://www.amezmo.com/docs/api/deployments/post); archive max 512MB |
| Deployments | Cancel | POST | `/instances/{instance_id}/deployments/{deployment_id}/cancel` | `instance_id`, `deployment_id` URI | 200 `{id,status}` | yes | destructive | [page](https://www.amezmo.com/docs/api/deployments/cancel-deployment) |
| Cron | List | GET | `/instances/{instance_id}/cron` | `instance_id` URI | 200 array of cron entry objects across all environments | yes | read-only | [page](https://www.amezmo.com/docs/api/cron/list-cron-entries) |
| Cron | Get | GET | `/instances/{instance_id}/cron/{cron_id}` | `instance_id`, `cron_id` URI | 200 cron entry: `id`, `name`, `command`, `expression`, `expression_alias`, `status`, `environment_name`, `log_file_path`, timestamps | yes | read-only | [page](https://www.amezmo.com/docs/api/cron/get-cron-entry) |
| Cron | Create | POST | `/instances/{instance_id}/cron` | required `name`, `command`, `expression`, `environment` | 201 cron entry | yes | mutating + confirmation | [page](https://www.amezmo.com/docs/api/cron/create-cron-entry); name is permanent; 409 `cron_entry_exists`, 422 `feature_not_supported` |
| Cron | Update | PATCH | `/instances/{instance_id}/cron/{cron_id}` | `command` and/or `expression` (at least one) | 200 cron entry | yes | mutating + confirmation | [page](https://www.amezmo.com/docs/api/cron/update-cron-entry); 409 `cron_entry_deleting`. Delete is dashboard-only |
| Regions | List | GET | `/regions` | none | 200 array of `{id, iso_country_code, name}` | yes | read-only | [page](https://www.amezmo.com/docs/api/regions/list-regions) |
| Regions | Get | GET | `/regions/{region_id}` | `region_id` URI | 200 region object | yes | read-only | [page](https://www.amezmo.com/docs/api/regions/get-region) |
| Workers | Get | GET | `/instances/{instance_id}/workers/{worker_id}` | `instance_id`, `worker_id` URI | 200 worker object: `id`, `name`, `command`, process/retry settings, status, log path | yes | read-only | [page](https://www.amezmo.com/docs/api/workers/get) |
| Workers | Start | POST | `/instances/{instance_id}/workers/{worker_id}/start` | URI IDs | 200 worker object | yes | mutating + confirmation | [page](https://www.amezmo.com/docs/api/workers/start) |
| Workers | Stop | POST | `/instances/{instance_id}/workers/{worker_id}/stop` | URI IDs | 200 worker object | yes | mutating + confirmation | [page](https://www.amezmo.com/docs/api/workers/stop) |
| Workers | Restart | POST | `/instances/{instance_id}/workers/{worker_id}/restart` | URI IDs | 200 worker object | yes | mutating + confirmation | [page](https://www.amezmo.com/docs/api/workers/restart) |

Errors (changed 2026-09-10) share one body: `http_status`, `type` (coarse class: `invalid_request_error`, `authentication_error`, `rate_limit_error`, `api_error`), `code` (specific reason, formerly in `type`), `message`, `errors` (per-parameter messages), `doc_url`, and `request_id`. The CLI prints `message`, each `errors` entry, `code`, and `request_id`. See [Errors](https://www.amezmo.com/docs/api/errors).

This inventory includes only operations documented by Amezmo. Tests use mocked HTTP responses; mutating commands have not been run against a live account.
