<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Http;

use InvalidArgumentException;

final readonly class EndpointRequest
{
    /**
     * @param list<string> $headers
     */
    public function __construct(
        public string $url,
        public string $method = 'GET',
        public array $headers = [],
        public ?string $body = null,
    ) {
        if (filter_var($this->url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('A valid endpoint URL is required.');
        }

        if (!in_array($this->method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'], true)) {
            throw new InvalidArgumentException('Unsupported HTTP method.');
        }
    }
}
