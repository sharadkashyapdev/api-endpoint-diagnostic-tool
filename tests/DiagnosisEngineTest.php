<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Tests;

use PHPUnit\Framework\TestCase;
use SharadKashyap\ApiEndpointDiagnosticTool\Diagnosis\DiagnosisEngine;
use SharadKashyap\ApiEndpointDiagnosticTool\Http\EndpointResult;

final class DiagnosisEngineTest extends TestCase
{
    public function testItDiagnosesMissingAuthentication(): void
    {
        $result = new EndpointResult(
            url: 'https://example.com/protected',
            reachable: true,
            statusCode: 401,
            contentType: 'application/json',
            responseBody: '{"error":"Unauthorized"}',
            transportError: null,
            durationMilliseconds: 25.4,
        );

        $report = (new DiagnosisEngine())->diagnose($result);

        self::assertFalse($report->requestSuccessful);
        self::assertTrue($report->endpointReachable);
        self::assertSame(401, $report->httpStatus);
        self::assertSame('JSON', $report->responseType);
        self::assertStringContainsString('credentials', $report->likelyCause);
        self::assertStringContainsString('Bearer token', $report->suggestedCheck);
    }

    public function testItRecognizesSuccessfulJsonResponse(): void
    {
        $result = new EndpointResult(
            url: 'https://example.com/api',
            reachable: true,
            statusCode: 200,
            contentType: 'application/json; charset=utf-8',
            responseBody: '{"status":"ok"}',
            transportError: null,
            durationMilliseconds: 18.2,
        );

        $report = (new DiagnosisEngine())->diagnose($result);

        self::assertTrue($report->requestSuccessful);
        self::assertSame('JSON', $report->responseType);
        self::assertSame('No corrective action is required.', $report->suggestedCheck);
    }

    public function testItReportsTransportFailure(): void
    {
        $result = new EndpointResult(
            url: 'https://unavailable.example',
            reachable: false,
            statusCode: null,
            contentType: null,
            responseBody: '',
            transportError: 'Could not resolve host',
            durationMilliseconds: 100.0,
        );

        $report = (new DiagnosisEngine())->diagnose($result);

        self::assertFalse($report->requestSuccessful);
        self::assertFalse($report->endpointReachable);
        self::assertNull($report->httpStatus);
        self::assertSame('No response', $report->responseType);
        self::assertSame('DNS resolution failed for the endpoint host.', $report->likelyCause);
        self::assertSame('Verify the hostname, DNS records, and local DNS resolver.', $report->suggestedCheck);
    }
}
