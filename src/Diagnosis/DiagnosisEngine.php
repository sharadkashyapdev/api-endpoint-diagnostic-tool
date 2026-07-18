<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Diagnosis;

use SharadKashyap\ApiEndpointDiagnosticTool\Http\EndpointResult;

final class DiagnosisEngine
{
    public function __construct(
        private readonly TransportFailureAnalyzer $transportFailureAnalyzer = new TransportFailureAnalyzer(),
        private readonly ResponseHeaderAnalyzer $responseHeaderAnalyzer = new ResponseHeaderAnalyzer(),
        private readonly ResponseConsistencyAnalyzer $responseConsistencyAnalyzer = new ResponseConsistencyAnalyzer(),
    ) {
    }

    public function diagnose(EndpointResult $result): DiagnosticReport
    {
        if (!$result->reachable) {
            $transportDiagnosis = $this->transportFailureAnalyzer->analyze($result->transportError);

            return new DiagnosticReport(
                requestSuccessful: false,
                endpointReachable: false,
                httpStatus: null,
                responseType: 'No response',
                likelyCause: $transportDiagnosis['likelyCause'],
                suggestedCheck: $transportDiagnosis['suggestedCheck'],
                durationMilliseconds: $result->durationMilliseconds,
            );
        }

        $statusCode = $result->statusCode ?? 0;
        [$likelyCause, $suggestedCheck] = $this->interpretStatus($statusCode);
        $suggestedCheck = $this->responseHeaderAnalyzer->refineSuggestion(
            statusCode: $statusCode,
            headers: $result->responseHeaders,
            suggestedCheck: $suggestedCheck,
        );

        $consistencyDiagnosis = $this->responseConsistencyAnalyzer->analyze(
            result: $result,
            likelyCause: $likelyCause,
            suggestedCheck: $suggestedCheck,
        );
        $likelyCause = $consistencyDiagnosis['likelyCause'];
        $suggestedCheck = $consistencyDiagnosis['suggestedCheck'];

        return new DiagnosticReport(
            requestSuccessful: $statusCode >= 200 && $statusCode < 300,
            endpointReachable: true,
            httpStatus: $statusCode,
            responseType: $this->detectResponseType($result),
            likelyCause: $likelyCause,
            suggestedCheck: $suggestedCheck,
            durationMilliseconds: $result->durationMilliseconds,
        );
    }

    /**
     * @return array{string, string}
     */
    private function interpretStatus(int $statusCode): array
    {
        return match ($statusCode) {
            200, 201, 202, 204 => [
                'The endpoint responded successfully.',
                'No corrective action is required.',
            ],
            400 => [
                'The request may contain invalid syntax or parameters.',
                'Verify the request body, query parameters, and expected schema.',
            ],
            401 => [
                'Authentication credentials are missing or invalid.',
                'Verify the Bearer token, API key, and Authorization header format.',
            ],
            403 => [
                'The credentials may be valid but lack permission.',
                'Verify account permissions, scopes, roles, and access policies.',
            ],
            404 => [
                'The requested endpoint or resource was not found.',
                'Verify the base URL, route path, resource identifier, and API version.',
            ],
            405 => [
                'The endpoint does not allow the request method.',
                'Verify whether the endpoint expects GET, POST, PUT, PATCH, or DELETE.',
            ],
            415 => [
                'The server does not support the submitted media type.',
                'Verify the Content-Type header and request body format.',
            ],
            422 => [
                'The request was understood but failed validation.',
                'Inspect validation errors and verify all required field values.',
            ],
            429 => [
                'The API rate limit may have been exceeded.',
                'Inspect rate-limit headers and retry after the permitted interval.',
            ],
            500 => [
                'The API encountered an internal server error.',
                'Inspect application logs, exceptions, and recent server-side changes.',
            ],
            502 => [
                'A gateway received an invalid response from an upstream service.',
                'Verify the upstream service, proxy configuration, and gateway logs.',
            ],
            503 => [
                'The service is temporarily unavailable.',
                'Check service health, capacity, maintenance status, and dependencies.',
            ],
            504 => [
                'A gateway timed out while waiting for an upstream service.',
                'Inspect upstream response time, proxy timeout settings, and dependencies.',
            ],
            default => $this->interpretStatusFamily($statusCode),
        };
    }

    /**
     * @return array{string, string}
     */
    private function interpretStatusFamily(int $statusCode): array
    {
        if ($statusCode >= 200 && $statusCode < 300) {
            return [
                'The endpoint responded successfully.',
                'No corrective action is required.',
            ];
        }

        if ($statusCode >= 300 && $statusCode < 400) {
            return [
                'The endpoint returned a redirection response.',
                'Inspect the Location header, redirect target, and client redirect policy.',
            ];
        }

        if ($statusCode >= 400 && $statusCode < 500) {
            return [
                'The endpoint rejected the request with a client error.',
                'Inspect the request method, headers, parameters, body, and API documentation.',
            ];
        }

        if ($statusCode >= 500 && $statusCode < 600) {
            return [
                'The endpoint failed while processing a valid request.',
                'Inspect server logs, service health, dependencies, and recent deployments.',
            ];
        }

        return [
            'The endpoint returned an unclassified HTTP status.',
            'Inspect the response body, headers, and API documentation.',
        ];
    }

    private function detectResponseType(EndpointResult $result): string
    {
        $contentType = strtolower($result->contentType ?? '');

        if (str_contains($contentType, 'json') || $this->containsJson($result->responseBody)) {
            return 'JSON';
        }

        if (str_contains($contentType, 'html')) {
            return 'HTML';
        }

        if (str_contains($contentType, 'xml')) {
            return 'XML';
        }

        if (str_contains($contentType, 'text')) {
            return 'Text';
        }

        return $result->contentType ?? 'Unknown';
    }

    private function containsJson(string $responseBody): bool
    {
        if ($responseBody === '') {
            return false;
        }

        json_decode($responseBody, true);

        return json_last_error() === JSON_ERROR_NONE;
    }
}
