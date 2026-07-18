<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Http;

final readonly class EndpointResult
{
    public function __construct(
        public string $url,
        public bool $reachable,
        public ?int $statusCode,
        public ?string $contentType,
        public string $responseBody,
        public ?string $transportError,
        public float $durationMilliseconds,
    ) {
    }
}