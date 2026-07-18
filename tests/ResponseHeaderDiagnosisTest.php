<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Tests;

use PHPUnit\Framework\TestCase;
use SharadKashyap\ApiEndpointDiagnosticTool\Diagnosis\DiagnosisEngine;
use SharadKashyap\ApiEndpointDiagnosticTool\Diagnosis\DiagnosticReport;
use SharadKashyap\ApiEndpointDiagnosticTool\Http\EndpointResult;

final class ResponseHeaderDiagnosisTest extends TestCase
{
    public function testItReportsAllowedMethodsFor405Response(): void
    {
        $report = $this->diagnose(
            statusCode: 405,
            headers: ['allow' => ['GET, POST']],
        );

        self::assertStringContainsString(
            'Server-reported allowed methods: GET, POST.',
            $report->suggestedCheck,
        );
    }

    public function testItReportsRetryGuidanceFor429Response(): void
    {
        $report = $this->diagnose(
            statusCode: 429,
            headers: ['retry-after' => ['120']],
        );

        self::assertStringContainsString(
            'Server retry guidance: wait 120.',
            $report->suggestedCheck,
        );
    }

    public function testItReportsAuthenticationSchemeFor401Response(): void
    {
        $report = $this->diagnose(
            statusCode: 401,
            headers: ['www-authenticate' => ['Bearer realm="api"']],
        );

        self::assertStringContainsString(
            'Server authentication scheme: Bearer.',
            $report->suggestedCheck,
        );
    }

    public function testItRemovesControlCharactersFromHeaderEvidence(): void
    {
        $report = $this->diagnose(
            statusCode: 405,
            headers: ['allow' => ["GET\r\nInjected: value"]],
        );

        self::assertStringNotContainsString("\r", $report->suggestedCheck);
        self::assertStringNotContainsString("\n", $report->suggestedCheck);
    }

    /**
     * @param array<string, list<string>> $headers
     */
    private function diagnose(int $statusCode, array $headers): DiagnosticReport
    {
        return (new DiagnosisEngine())->diagnose(
            new EndpointResult(
                url: 'https://example.com/api',
                reachable: true,
                statusCode: $statusCode,
                contentType: 'application/json',
                responseBody: '{}',
                transportError: null,
                durationMilliseconds: 10.0,
                responseHeaders: $headers,
            ),
        );
    }
}