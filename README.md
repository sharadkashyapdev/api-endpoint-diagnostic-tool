# API Endpoint Diagnostic Tool

[![Quality](https://github.com/sharadkashyapdev/api-endpoint-diagnostic-tool/actions/workflows/quality.yml/badge.svg)](https://github.com/sharadkashyapdev/api-endpoint-diagnostic-tool/actions/workflows/quality.yml)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A framework-independent PHP tool that checks API endpoints, explains likely failure causes, and recommends the next diagnostic action.

> Project status: Under active development.

## Key Capabilities

- Checks endpoint reachability, HTTP status, response type, and duration
- Supports configurable methods, headers, and request bodies
- Classifies DNS, timeout, SSL/TLS, and refused connections
- Interprets specific HTTP errors and complete status families
- Uses `Allow`, `Retry-After`, and `WWW-Authenticate` as evidence
- Detects malformed JSON, unexpected HTML, and invalid 204 bodies
- Produces human-readable text or structured JSON
- Redacts tokens, API keys, passwords, and URL credentials
- Restricts requests and redirects to HTTP and HTTPS
- Includes PHPUnit, PHPStan level 8, and GitHub Actions quality gates

## Requirements

- PHP 8.2 or later
- PHP cURL extension
- Composer 2

## Installation

```bash
git clone https://github.com/sharadkashyapdev/api-endpoint-diagnostic-tool.git
cd api-endpoint-diagnostic-tool
composer install
```

## CLI Usage

Human-readable diagnosis:

```bash
php bin/diagnose.php https://example.com/api/endpoint
```

Structured JSON:

```bash
php bin/diagnose.php https://example.com/api/endpoint --json
```

Example output:

```text
Request Status: Failed
Endpoint Reachable: Yes
HTTP Status: 401
Response Type: JSON
Likely Cause: Authentication credentials are missing or invalid.
Suggested Check: Verify the Bearer token, API key, and Authorization header format.
Duration: 125.4 ms
```

## Programmatic Usage

```php
<?php

require 'vendor/autoload.php';

use SharadKashyap\ApiEndpointDiagnosticTool\Diagnosis\DiagnosisEngine;
use SharadKashyap\ApiEndpointDiagnosticTool\Http\CurlEndpointProbe;
use SharadKashyap\ApiEndpointDiagnosticTool\Http\EndpointRequest;

$request = new EndpointRequest(
    url: 'https://example.com/api/users',
    method: 'POST',
    headers: [
        'Accept: application/json',
        'Content-Type: application/json',
    ],
    body: '{"name":"Sharad"}',
);

$result = (new CurlEndpointProbe())->probe($request);
$report = (new DiagnosisEngine())->diagnose($result);

print_r($report->toArray());
```

## Diagnostic Coverage

| Evidence | Suggested investigation |
|---|---|
| DNS failure | Hostname and DNS records |
| Timeout | Availability, latency, and timeout settings |
| SSL/TLS failure | Certificate chain, hostname, and expiry |
| Connection refused | Host, port, firewall, and service state |
| 400 / 422 | Request syntax, schema, and validation |
| 401 / 403 | Authentication, scopes, and permissions |
| 404 / 405 | Route, API version, and HTTP method |
| 415 | Content type and body format |
| 429 | Rate limits and retry interval |
| 5xx | Server, gateway, upstream service, and dependencies |

## Security

- Only HTTP and HTTPS URLs are accepted.
- Sensitive values are redacted from reports.
- Redirect depth and request duration are limited.
- Use only endpoints and credentials you are authorized to test.
- The tool diagnoses services; it does not modify or repair them.

## Quality

```bash
composer quality
```

This runs:

- 36 automated tests with 91 assertions
- PHPStan level 8 analysis
- GitHub Actions checks across PHP 8.2–8.5

## Architecture

- `src/Http` — request execution and evidence capture
- `src/Diagnosis` — diagnostic intelligence
- `src/Output` — structured and secure output
- `bin/diagnose.php` — CLI entry point
- `tests` — unit, security, and integration coverage

## Scope

The tool identifies likely causes and recommends checks. It does not bypass authorization, change remote configuration, guarantee a root cause without server-side evidence, or automatically repair endpoints.

## License

Released under the [MIT License](LICENSE).

## Author

Sharad Kashyap — [@sharadkashyapdev](https://github.com/sharadkashyapdev)
