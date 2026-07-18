<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Tests;

use PHPUnit\Framework\TestCase;
use SharadKashyap\ApiEndpointDiagnosticTool\Diagnosis\DiagnosticReport;
use SharadKashyap\ApiEndpointDiagnosticTool\Output\JsonReportFormatter;
use SharadKashyap\ApiEndpointDiagnosticTool\Output\SensitiveDataRedactor;

final class OutputSecurityTest extends TestCase
{
    private SensitiveDataRedactor $redactor;

    protected function setUp(): void
    {
        $this->redactor = new SensitiveDataRedactor();
    }

    public function testItRedactsBearerCredential(): void
    {
        $output = $this->redactor->redact(
            'Authorization: Bearer abcdefghijklmnopqrstuvwxyz123456',
        );

        self::assertSame('Authorization: Bearer [REDACTED]', $output);
    }

    public function testItRedactsSensitiveQueryParameters(): void
    {
        $output = $this->redactor->redact(
            'https://example.com/api?token=secret-token&api_key=secret-key',
        );

        self::assertStringNotContainsString('secret-token', $output);
        self::assertStringNotContainsString('secret-key', $output);
        self::assertSame(2, substr_count($output, '[REDACTED]'));
    }

    public function testItRedactsUrlCredentials(): void
    {
        $output = $this->redactor->redact(
            'Failed to connect to https://user:password@example.com/api',
        );

        self::assertStringNotContainsString('user:password', $output);
        self::assertStringContainsString('https://[REDACTED]@example.com/api', $output);
    }

    public function testItPreservesNonSecretDiagnosticGuidance(): void
    {
        $guidance = 'Verify the Bearer token, API key, and Authorization header format.';

        self::assertSame($guidance, $this->redactor->redact($guidance));
    }

    public function testJsonFormatterNeverExposesEmbeddedCredential(): void
    {
        $report = new DiagnosticReport(
            requestSuccessful: false,
            endpointReachable: false,
            httpStatus: null,
            responseType: 'No response',
            likelyCause: 'Request failed with Bearer abcdefghijklmnopqrstuvwxyz123456',
            suggestedCheck: 'Verify credentials.',
            durationMilliseconds: 10.0,
        );

        $json = (new JsonReportFormatter())->format($report);

        self::assertStringNotContainsString('abcdefghijklmnopqrstuvwxyz123456', $json);
        self::assertStringContainsString('[REDACTED]', $json);
    }
}