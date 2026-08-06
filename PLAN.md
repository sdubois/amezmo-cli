# Plan

## Architecture

Standalone Symfony Console application with focused commands, a reusable Symfony HttpClient wrapper, file-backed credentials, typed configuration, and a renderer for table/JSON/YAML output. Resource-specific API services will own endpoint paths.

## MVP scope

Version, authentication login/status/logout, whoami, secret redaction, `/me/` validation, endpoint inventory, tests, static analysis, linting, and PHAR packaging.

## Deferred scope

Resource commands whose exact endpoint pages and response examples need further inventory; all mutating/destructive operations and archive upload commands.

## Security decisions

Bearer keys are never logged or printed in full. Unix config files use mode 0600. `AMEZMO_API_KEY` is preferred for CI. Windows ACL hardening is not claimed.

## Risks and next steps

The live docs index links additional endpoint pages but currently exposes incomplete path/response details for several resources. `regions list` is now implemented from its fully documented child page; inventory the remaining child pages next.
