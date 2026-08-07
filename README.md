# amezmo-cli

`amezmo-cli` is an unofficial, open-source community command-line client for the Amezmo REST API. It is not an official Amezmo product.

Status: early release. Version reporting, file-backed authentication, current-account validation, JSON/YAML/table rendering, and read-only account, region, instance, environment, instance-type, and deployment commands are implemented.

Requires PHP 8.2+.

## Install

From source or Composer:

```bash
composer global require your-vendor/amezmo-cli
amezmo-cli --version
```

Build a PHAR with `composer build`; use `php build/amezmo-cli.phar --version`. On macOS/Linux it can be made executable and placed in `~/.local/bin` or `/usr/local/bin`. On Windows, run it with `php amezmo-cli.phar --version`.

## Authentication

For CI and automation, prefer `AMEZMO_API_KEY`:

```bash
export AMEZMO_API_KEY="your-api-key"
amezmo-cli whoami
```

Interactive login stores the key in a user-specific JSON file:

- Linux: `$XDG_CONFIG_HOME/amezmo-cli/config.json`, or `~/.config/amezmo-cli/config.json`
- macOS: `~/Library/Application Support/amezmo-cli/config.json`
- Windows: `%APPDATA%\amezmo-cli\config.json`

```bash
amezmo-cli auth login
amezmo-cli auth status
amezmo-cli auth logout
```

Unix config files are written with user-only permissions. Windows ACL hardening is not claimed yet. Keys are never printed in full or included in debug logs.

## Options and output

Configuration precedence is command line, environment, stored configuration, then defaults. Supported variables are `AMEZMO_API_URL`, `AMEZMO_API_KEY`, `AMEZMO_OUTPUT`, and `AMEZMO_TIMEOUT`. Options include `--api-url`, `--api-key`, `--output`/`-o`, `--timeout`, `--debug`, `--no-interaction`, and `--no-color`. `NO_COLOR` is respected.

```bash
amezmo-cli whoami -o table
amezmo-cli whoami --output=json
amezmo-cli whoami --output=yaml
amezmo-cli regions list
amezmo-cli regions list --output=json
amezmo-cli regions get lb3-US
amezmo-cli instances list
amezmo-cli instances list --instance-type=business
amezmo-cli instances get 3503
amezmo-cli instance-types list
amezmo-cli environments list 3503
amezmo-cli environments get 3503 production
amezmo-cli deployments get 3503 49015
amezmo-cli workers get 3503 <worker-id>
amezmo-cli config path
amezmo-cli doctor
```

Diagnostics go to stderr; machine-readable output contains only the API result.

## Development

```bash
composer install
composer test
composer analyse
composer lint
composer build
composer verify-phar
```

Tests never call the live API. See [PLAN.md](PLAN.md) and [docs/api-endpoints.md](docs/api-endpoints.md) for scope, source inventory, and open API questions. Packagist, Homebrew, Scoop, and Chocolatey publication are future work.
