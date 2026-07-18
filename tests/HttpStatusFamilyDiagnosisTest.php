<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Tests;

use PHPUnit\Framework\TestCase;
use SharadKashyap\ApiEndpointDiagnosticTool\Diagnosis\DiagnosisEngine;
use SharadKashyap\ApiEndpointDiagnosticTool\Diagnosis\DiagnosticReport;
use SharadKashyap\ApiEndpointDiagnosticTool\Http\EndpointResult;

final class HttpStatusFamilyDiagnosisTest extends TestCase
{
    public function testItRecognizesAnySuccessful2xxStatus(): void
    {
        $report = $this->diagnoseStatus(206);

        self::assertTrue($report->requestSuccessful);
        self::assertSame('The endpoint responded successfully.', $report->likelyCause);
        self::assertSame('No corrective action is required.', $report->suggestedCheck);
    }

    public function testItClassifiesRedirectStatus(): void
    {
        $report = $this->diagnoseStatus(304);

        self::assertFalse($report->requestSuccessful);
        self::assertSame('The endpoint returned a redirection response.', $report->likelyCause);
        self::assertStringContainsString('Location header', $report->suggestedCheck);
    }

    public function testItClassifiesUnknownClientError(): void
    {
        $report = $this->diagnoseStatus(418);

        self::assertSame(
            'The endpoint rejected the request with a client error.',
            $report->likelyCause,
        );
        self::assertStringContainsString('request method', $report->suggestedCheck);
    }

    public function testItClassifiesUnknownServerError(): void
    {
        $report = $this->diagnoseStatus(507);

        self::assertSame(
            'The endpoint failed while processing a valid request.',
            $report->likelyCause,
        );
        self::assertStringContainsString('server logs', $report->suggestedCheck);
    }

    private function diagnoseStatus(int $statusCode): DiagnosticReport
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
            ),
        );
    }
}