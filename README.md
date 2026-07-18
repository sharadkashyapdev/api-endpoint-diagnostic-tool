# API Endpoint Diagnostic Tool

A framework-independent PHP CLI tool that checks an API endpoint, identifies likely failure causes, and recommends the next diagnostic action.

> Project status: Under active development.

## Current Capabilities

- Checks whether an endpoint is reachable
- Reports HTTP status and response duration
- Detects JSON, HTML, XML, and text responses
- Interprets common API failure status codes
- Suggests an actionable next check
- Distinguishes transport failures from HTTP failures
- Follows redirects safely up to five levels
- Includes automated diagnosis tests

## Requirements

- PHP 8.2 or later
- PHP cURL extension
- Composer

## Installation

```bash
git clone https://github.com/sharadkashyapdev/api-endpoint-diagnostic-tool.git
cd api-endpoint-diagnostic-tool
composer install
```

## Usage

```bash
php bin/diagnose.php https://example.com/api/endpoint
```

Example diagnostic output:

```text
Request Status: Failed
Endpoint Reachable: Yes
HTTP Status: 401
Response Type: JSON
Likely Cause: Authentication credentials are missing or invalid.
Suggested Check: Verify the Bearer token, API key, and Authorization header format.
Duration: 125.4 ms
```

## Supported Diagnoses

- 400 Bad Request
- 401 Unauthorized
- 403 Forbidden
- 404 Not Found
- 405 Method Not Allowed
- 415 Unsupported Media Type
- 422 Unprocessable Content
- 429 Too Many Requests
- 500 Internal Server Error
- 502 Bad Gateway
- 503 Service Unavailable
- 504 Gateway Timeout
- DNS, connection, SSL, and timeout failures

## Architecture

- `src/Http` captures raw endpoint evidence.
- `src/Diagnosis` produces an actionable report.
- `bin/diagnose.php` provides the CLI.
- `tests` verifies behavior without network dependency.

## Tests

```bash
php vendor/bin/phpunit tests
```

## License

MIT
