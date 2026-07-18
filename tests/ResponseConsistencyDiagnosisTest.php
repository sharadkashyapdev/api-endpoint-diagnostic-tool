<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Tests;

use PHPUnit\Framework\TestCase;
use SharadKashyap\ApiEndpointDiagnosticTool\Diagnosis\DiagnosisEngine;
use SharadKashyap\ApiEndpointDiagnosticTool\Diagnosis\DiagnosticReport;
use SharadKashyap\ApiEndpointDiagnosticTool\Http\EndpointResult;

final class ResponseConsistencyDiagnosisTest extends TestCase
{
    public function testItDetectsMalformedDeclaredJson(): void
    {
        $report = $this->diagnose(
            statusCode: 200,
            contentType: 'application/json',
            responseBody: '{"status":',
        );

        self::assertSame(
            'The endpoint declared JSON but returned a malformed JSON body.',
            $report->likelyCause,
        );
        self::assertStringContainsString('JSON serialization', $report->suggestedCheck);
    }

    public function testItDetectsHtmlReturnedBySuccessfulApiRequest(): void
    {
        $report = $this->diagnose(
            statusCode: 200,
            contentType: 'text/html',
            responseBody: '<html><body>Login</body></html>',
        );

        self::assertSame(
            'The endpoint succeeded but returned HTML instead of an API response.',
            $report->likelyCause,
        );
        self::assertStringContainsString('API route', $report->suggestedCheck);
    }

    public function testItDetectsUnexpectedBodyOn204Response(): void
    {
        $report = $this->diagnose(
            statusCode: 204,
            contentType: null,
            responseBody: 'unexpected',
        );

        self::assertSame(
            'The HTTP 204 response unexpectedly included a response body.',
            $report->likelyCause,
        );
        self::assertStringContainsString('permits response content', $report->suggestedCheck);
    }

    public function testItPreservesConsistentSuccessfulJsonDiagnosis(): void
    {
        $report = $this->diagnose(
            statusCode: 200,
            contentType: 'application/json',
            responseBody: '{"status":"ok"}',
        );

        self::assertSame('The endpoint responded successfully.', $report->likelyCause);
        self::assertSame('No corrective action is required.', $report->suggestedCheck);
    }

    private function diagnose(
        int $statusCode,
        ?string $contentType,
        string $responseBody,
    ): DiagnosticReport {
        return (new DiagnosisEngine())->diagnose(
            new EndpointResult(
                url: 'https://example.com/api',
                reachable: true,
                statusCode: $statusCode,
                contentType: $contentType,
                responseBody: $responseBody,
                transportError: null,
                durationMilliseconds: 10.0,
            ),
        );
    }
}