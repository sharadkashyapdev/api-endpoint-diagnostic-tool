<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Http;

use InvalidArgumentException;

final readonly class CurlEndpointProbe implements EndpointProbe
{
    public function __construct(
        private int $timeoutSeconds = 10,
    ) {
    }

    public function probe(string $url): EndpointResult
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('A valid endpoint URL is required.');
        }

        $handle = curl_init($url);

        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_CONNECTTIMEOUT => $this->timeoutSeconds,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'User-Agent: API-Endpoint-Diagnostic-Tool/0.1',
            ],
        ]);

        $responseBody = curl_exec($handle);
        $transportError = curl_errno($handle) !== 0
            ? curl_error($handle)
            : null;

        $statusCode = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $contentType = curl_getinfo($handle, CURLINFO_CONTENT_TYPE);
        $durationSeconds = curl_getinfo($handle, CURLINFO_TOTAL_TIME);

        curl_close($handle);

        return new EndpointResult(
            url: $url,
            reachable: $transportError === null && $statusCode > 0,
            statusCode: $statusCode > 0 ? $statusCode : null,
            contentType: is_string($contentType) ? $contentType : null,
            responseBody: is_string($responseBody) ? $responseBody : '',
            transportError: $transportError,
            durationMilliseconds: round($durationSeconds * 1000, 2),
        );
    }
}