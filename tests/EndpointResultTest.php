<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Tests;

use PHPUnit\Framework\TestCase;
use SharadKashyap\ApiEndpointDiagnosticTool\Http\EndpointResult;

final class EndpointResultTest extends TestCase
{
    public function testItStoresCompleteResponseEvidence(): void
    {
        $result = new EndpointResult(
            url: 'https://example.com/start',
            reachable: true,
            statusCode: 200,
            contentType: 'application/json',
            responseBody: '{"status":"ok"}',
            transportError: null,
            durationMilliseconds: 42.5,
            effectiveUrl: 'https://example.com/final',
            responseHeaders: [
                'content-type' => ['application/json'],
                'x-request-id' => ['request-123'],
            ],
        );

        self::assertSame('https://example.com/start', $result->url);
        self::assertSame('https://example.com/final', $result->effectiveUrl);
        self::assertSame(['application/json'], $result->responseHeaders['content-type']);
        self::assertSame(['request-123'], $result->responseHeaders['x-request-id']);
    }
}