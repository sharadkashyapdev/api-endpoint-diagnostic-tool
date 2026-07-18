<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SharadKashyap\ApiEndpointDiagnosticTool\Http\EndpointRequest;

final class EndpointRequestTest extends TestCase
{
    public function testItStoresRequestConfiguration(): void
    {
        $request = new EndpointRequest(
            url: 'https://example.com/users',
            method: 'POST',
            headers: ['Authorization: Bearer token', 'Content-Type: application/json'],
            body: '{"name":"Sharad"}',
        );

        self::assertSame('https://example.com/users', $request->url);
        self::assertSame('POST', $request->method);
        self::assertSame(
            ['Authorization: Bearer token', 'Content-Type: application/json'],
            $request->headers,
        );
        self::assertSame('{"name":"Sharad"}', $request->body);
    }

    public function testItRejectsInvalidUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A valid endpoint URL is required.');

        new EndpointRequest(url: 'not-a-url');
    }

    public function testItRejectsUnsupportedHttpMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported HTTP method.');

        new EndpointRequest(
            url: 'https://example.com/api',
            method: 'TRACE',
        );
    }
}