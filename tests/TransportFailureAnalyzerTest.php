<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Tests;

use PHPUnit\Framework\TestCase;
use SharadKashyap\ApiEndpointDiagnosticTool\Diagnosis\TransportFailureAnalyzer;

final class TransportFailureAnalyzerTest extends TestCase
{
    private TransportFailureAnalyzer $analyzer;

    protected function setUp(): void
    {
        $this->analyzer = new TransportFailureAnalyzer();
    }

    public function testItClassifiesTimeoutFailure(): void
    {
        $diagnosis = $this->analyzer->analyze('Operation timed out after 10000 milliseconds');

        self::assertSame(
            'The endpoint did not respond before the request timed out.',
            $diagnosis['likelyCause'],
        );
        self::assertStringContainsString('timeout configuration', $diagnosis['suggestedCheck']);
    }

    public function testItClassifiesSslFailure(): void
    {
        $diagnosis = $this->analyzer->analyze('SSL certificate problem: certificate has expired');

        self::assertSame(
            'SSL/TLS validation or negotiation failed.',
            $diagnosis['likelyCause'],
        );
        self::assertStringContainsString('certificate chain', $diagnosis['suggestedCheck']);
    }

    public function testItClassifiesRefusedConnection(): void
    {
        $diagnosis = $this->analyzer->analyze('Failed to connect: Connection refused');

        self::assertSame(
            'The server refused or could not accept the connection.',
            $diagnosis['likelyCause'],
        );
        self::assertStringContainsString('firewall rules', $diagnosis['suggestedCheck']);
    }

    public function testItPreservesUnknownTransportError(): void
    {
        $diagnosis = $this->analyzer->analyze('Unexpected transport failure');

        self::assertSame('Unexpected transport failure', $diagnosis['likelyCause']);
        self::assertStringContainsString('network connection', $diagnosis['suggestedCheck']);
    }
}