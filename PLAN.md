# Plan

## Architecture

Standalone Symfony Console application with focused commands, a reusable Symfony HttpClient wrapper, file-backed credentials, typed configuration, and a renderer for table/JSON/YAML output. Resource-specific API services will own endpoint paths.

## MVP scope

Version, authentication login/status/logout, whoami, read-only region/instance/instance-type/environment/deployment commands, secret redaction, endpoint inventory, tests, static analysis, linting, and PHAR packaging.

## Deferred scope

Instance creation, instance termination, environment updates, deployment creation/cancellation, worker start/stop/restart, and archive upload commands.

Shared confirmation support is now available for future mutating commands. It requires interactive confirmation by default, refuses non-interactive execution unless `--yes` is supplied, and is not attached to read-only commands.

## Security decisions

Bearer keys are never logged or printed in full. Unix config files use mode 0600. `AMEZMO_API_KEY` is preferred for CI. Windows ACL hardening is not claimed.

## Risks and next steps

The live docs index links additional endpoint pages but currently exposes incomplete path/response details for several resources. The current read-only batch is implemented from documented child pages; inventory worker and remaining mutating child pages next.
