<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Http;

final readonly class CurlEndpointProbe implements EndpointProbe
{
    public function __construct(
        private int $timeoutSeconds = 10,
    ) {
    }

    public function probe(EndpointRequest $request): EndpointResult
    {
        $handle = curl_init($request->url);

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_CONNECTTIMEOUT => $this->timeoutSeconds,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_CUSTOMREQUEST => $request->method,
            CURLOPT_HTTPHEADER => array_merge(
                [
                    'Accept: application/json',
                    'User-Agent: API-Endpoint-Diagnostic-Tool/0.1',
                ],
                $request->headers,
            ),
        ];

        if ($request->body !== null) {
            $options[CURLOPT_POSTFIELDS] = $request->body;
        }

        curl_setopt_array($handle, $options);

        $responseBody = curl_exec($handle);
        $transportError = curl_errno($handle) !== 0
            ? curl_error($handle)
            : null;

        $statusCode = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $contentType = curl_getinfo($handle, CURLINFO_CONTENT_TYPE);
        $durationSeconds = curl_getinfo($handle, CURLINFO_TOTAL_TIME);

        curl_close($handle);

        return new EndpointResult(
            url: $request->url,
            reachable: $transportError === null && $statusCode > 0,
            statusCode: $statusCode > 0 ? $statusCode : null,
            contentType: is_string($contentType) ? $contentType : null,
            responseBody: is_string($responseBody) ? $responseBody : '',
            transportError: $transportError,
            durationMilliseconds: round($durationSeconds * 1000, 2),
        );
    }
}