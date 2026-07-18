<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Tests;

use PHPUnit\Framework\TestCase;
use SharadKashyap\ApiEndpointDiagnosticTool\Diagnosis\DiagnosticReport;
use SharadKashyap\ApiEndpointDiagnosticTool\Output\JsonReportFormatter;

final class JsonReportFormatterTest extends TestCase
{
    public function testItFormatsCompleteDiagnosticReportAsJson(): void
    {
        $report = new DiagnosticReport(
            requestSuccessful: false,
            endpointReachable: true,
            httpStatus: 401,
            responseType: 'JSON',
            likelyCause: 'Authentication credentials are missing or invalid.',
            suggestedCheck: 'Verify the Bearer token.',
            durationMilliseconds: 25.4,
        );

        $json = (new JsonReportFormatter())->format($report);
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        self::assertFalse($decoded['request_successful']);
        self::assertTrue($decoded['endpoint_reachable']);
        self::assertSame(401, $decoded['http_status']);
        self::assertSame('JSON', $decoded['response_type']);
        self::assertSame(
            'Authentication credentials are missing or invalid.',
            $decoded['likely_cause'],
        );
        self::assertSame('Verify the Bearer token.', $decoded['suggested_check']);
        self::assertSame(25.4, $decoded['duration_milliseconds']);
        self::assertStringContainsString("\n", $json);
    }
}