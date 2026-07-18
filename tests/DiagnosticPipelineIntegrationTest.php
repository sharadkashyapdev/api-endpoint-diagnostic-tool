<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Tests;

use PHPUnit\Framework\TestCase;
use SharadKashyap\ApiEndpointDiagnosticTool\Diagnosis\DiagnosisEngine;
use SharadKashyap\ApiEndpointDiagnosticTool\Http\EndpointResult;
use SharadKashyap\ApiEndpointDiagnosticTool\Output\JsonReportFormatter;

final class DiagnosticPipelineIntegrationTest extends TestCase
{
    public function testAuthenticationFailureFlowsThroughHeaderIntelligenceAndJsonOutput(): void
    {
        $output = $this->diagnoseToJson(
            new EndpointResult(
                url: 'https://example.com/protected',
                reachable: true,
                statusCode: 401,
                contentType: 'application/json',
                responseBody: '{"error":"unauthorized"}',
                transportError: null,
                durationMilliseconds: 20.0,
                responseHeaders: [
                    'www-authenticate' => ['Bearer realm="api"'],
                ],
            ),
        );

        self::assertFalse($output['request_successful']);
        self::assertTrue($output['endpoint_reachable']);
        self::assertSame(401, $output['http_status']);
        self::assertStringContainsString(
            'Server authentication scheme: Bearer.',
            $output['suggested_check'],
        );
    }

    public function testTransportErrorSecretIsRedactedFromFinalJson(): void
    {
        $report = (new DiagnosisEngine())->diagnose(
            new EndpointResult(
                url: 'https://example.com/api',
                reachable: false,
                statusCode: null,
                contentType: null,
                responseBody: '',
                transportError: 'Request failed at https://example.com/api?token=top-secret-value',
                durationMilliseconds: 10000.0,
            ),
        );

        $json = (new JsonReportFormatter())->format($report);

        self::assertStringNotContainsString('top-secret-value', $json);
        self::assertStringContainsString('token=[REDACTED]', $json);
    }

    public function testMalformedJsonDiagnosisFlowsToStructuredOutput(): void
    {
        $output = $this->diagnoseToJson(
            new EndpointResult(
                url: 'https://example.com/api',
                reachable: true,
                statusCode: 200,
                contentType: 'application/json',
                responseBody: '{"status":',
                transportError: null,
                durationMilliseconds: 15.0,
            ),
        );

        self::assertTrue($output['request_successful']);
        self::assertSame('JSON', $output['response_type']);
        self::assertSame(
            'The endpoint declared JSON but returned a malformed JSON body.',
            $output['likely_cause'],
        );
    }

    public function testAllowedMethodsFlowToStructuredOutput(): void
    {
        $output = $this->diagnoseToJson(
            new EndpointResult(
                url: 'https://example.com/api',
                reachable: true,
                statusCode: 405,
                contentType: 'application/json',
                responseBody: '{}',
                transportError: null,
                durationMilliseconds: 12.0,
                responseHeaders: [
                    'allow' => ['GET, POST'],
                ],
            ),
        );

        self::assertSame(405, $output['http_status']);
        self::assertStringContainsString(
            'Server-reported allowed methods: GET, POST.',
            $output['suggested_check'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function diagnoseToJson(EndpointResult $result): array
    {
        $report = (new DiagnosisEngine())->diagnose($result);
        $json = (new JsonReportFormatter())->format($report);

        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }
}