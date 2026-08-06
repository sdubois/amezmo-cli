# Amezmo API endpoint inventory

Source: [official Amezmo API documentation](https://www.amezmo.com/docs/api), inspected 2026-08-06. The documented base URL is `https://api.amezmo.com/v1`; requests require a Bearer token and return JSON.

| Resource | Operation | Method | Path | Parameters/body | Response | Auth | Safety | Documentation / notes |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Accounts | Get current user | GET | `/me/` | none | 200 object: `team_context`, `instance_limit`, `email` | yes | read-only | [page](https://www.amezmo.com/docs/api/accounts/get-the-current-user) |
| Instances | List instances | GET | documented from Instances index | page does not expose path/response in captured content | undocumented here | yes | read-only | [index](https://www.amezmo.com/docs/api/instances) |
| Instances | Get instance | GET | documented from Instances index | path/response not exposed in captured content | undocumented here | yes | read-only | [index](https://www.amezmo.com/docs/api/instances) |
| Instances | List instance types | GET | documented from Instances index | path/response not exposed in captured content | undocumented here | yes | read-only | [index](https://www.amezmo.com/docs/api/instances) |
| Instances | Create instance | POST | documented from Instances index | request fields not inventoried | undocumented here | yes | mutating | [index](https://www.amezmo.com/docs/api/instances) |
| Instances | Terminate instance | DELETE | documented from Instances index | path/response not inventoried | undocumented here | yes | destructive | [index](https://www.amezmo.com/docs/api/instances) |
| Environments | List | GET | `/instances/{instance_id}/environments` | `instance_id` URI | 200 array of environment objects | yes | read-only | [page](https://www.amezmo.com/docs/api/environments/list-environments) |
| Environments | Get | GET | `/instances/{instance_id}/environments/{name}` | `instance_id`, `name` URI | 200 environment object | yes | read-only | [page](https://www.amezmo.com/docs/api/environments/get-environment) |
| Environments | Update | PUT/PATCH | documented from Environments index | fields not inventoried | undocumented here | yes | mutating | [index](https://www.amezmo.com/docs/api/environments) |
| Deployments | Get | GET | `/instances/{instance_id}/deployments/{deployment_id}` | URI IDs | 200 `{id,status}` | yes | read-only | [page](https://www.amezmo.com/docs/api/deployments/get) |
| Deployments | Create | POST | `/instances/{instance_id}/deployments` | multipart: `environment`, `archive`, optional repo metadata | 201 `{id,status}` | yes | mutating | [page](https://www.amezmo.com/docs/api/deployments/post); archive max 512MB |
| Deployments | Cancel | POST | documented from Deployments index | path/body not exposed in captured content | undocumented here | yes | destructive | [index](https://www.amezmo.com/docs/api/deployments) |
| Regions | List/Get | GET | documented from Regions index | paths/shape not exposed in captured content | undocumented here | yes | read-only | [index](https://www.amezmo.com/docs/api/regions) |
| Workers | Get/Start/Stop/Restart | GET/POST | documented from Workers index | paths/shape not exposed in captured content | undocumented here | yes | read-only or mutating | [index](https://www.amezmo.com/docs/api/workers) |

Open questions are intentionally retained rather than filled with guesses. The initial CLI slice only uses the fully documented `/me/` operation.
