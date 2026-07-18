<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SharadKashyap\ApiEndpointDiagnosticTool\Http\EndpointRequest;

final class EndpointProtocolSecurityTest extends TestCase
{
    public function testItRejectsFileProtocol(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only HTTP and HTTPS endpoint URLs are supported.');

        new EndpointRequest('file:///etc/passwd');
    }

    public function testItRejectsFtpProtocol(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only HTTP and HTTPS endpoint URLs are supported.');

        new EndpointRequest('ftp://example.com/file.txt');
    }

    public function testItAcceptsHttpsProtocol(): void
    {
        $request = new EndpointRequest('https://example.com/api');

        self::assertSame('https://example.com/api', $request->url);
    }
}