<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Diagnosis;

use SharadKashyap\ApiEndpointDiagnosticTool\Http\EndpointResult;

final class ResponseConsistencyAnalyzer
{
    /**
     * @return array{likelyCause: string, suggestedCheck: string}
     */
    public function analyze(
        EndpointResult $result,
        string $likelyCause,
        string $suggestedCheck,
    ): array {
        $statusCode = $result->statusCode ?? 0;
        $contentType = strtolower($result->contentType ?? '');
        $body = trim($result->responseBody);

        if ($statusCode === 204 && $body !== '') {
            return [
                'likelyCause' => 'The HTTP 204 response unexpectedly included a response body.',
                'suggestedCheck' => 'Remove the body from the 204 response or return a status that permits response content.',
            ];
        }

        if (
            str_contains($contentType, 'json')
            && $body !== ''
            && !$this->isValidJson($body)
        ) {
            return [
                'likelyCause' => 'The endpoint declared JSON but returned a malformed JSON body.',
                'suggestedCheck' => 'Verify server-side JSON serialization and inspect middleware or proxy output.',
            ];
        }

        if (
            $statusCode >= 200
            && $statusCode < 300
            && (
                str_contains($contentType, 'html')
                || str_starts_with(strtolower($body), '<!doctype html')
                || str_starts_with(strtolower($body), '<html')
            )
        ) {
            return [
                'likelyCause' => 'The endpoint succeeded but returned HTML instead of an API response.',
                'suggestedCheck' => 'Verify the API route, Accept header, authentication flow, and proxy configuration.',
            ];
        }

        return [
            'likelyCause' => $likelyCause,
            'suggestedCheck' => $suggestedCheck,
        ];
    }

    private function isValidJson(string $body): bool
    {
        json_decode($body, true);

        return json_last_error() === JSON_ERROR_NONE;
    }
}