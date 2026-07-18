<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Diagnosis;

final class ResponseHeaderAnalyzer
{
    /**
     * @param array<string, list<string>> $headers
     */
    public function refineSuggestion(
        int $statusCode,
        array $headers,
        string $suggestedCheck,
    ): string {
        if ($statusCode === 405) {
            $allowedMethods = $this->firstHeader($headers, 'allow');

            if ($allowedMethods !== null) {
                return $suggestedCheck . ' Server-reported allowed methods: ' . $allowedMethods . '.';
            }
        }

        if ($statusCode === 429) {
            $retryAfter = $this->firstHeader($headers, 'retry-after');

            if ($retryAfter !== null) {
                return $suggestedCheck . ' Server retry guidance: wait ' . $retryAfter . '.';
            }
        }

        if ($statusCode === 401) {
            $challenge = $this->firstHeader($headers, 'www-authenticate');

            if (
                $challenge !== null
                && preg_match('/^([A-Za-z][A-Za-z0-9+.-]*)/', $challenge, $matches) === 1
            ) {
                return $suggestedCheck . ' Server authentication scheme: ' . $matches[1] . '.';
            }
        }

        return $suggestedCheck;
    }

    /**
     * @param array<string, list<string>> $headers
     */
    private function firstHeader(array $headers, string $targetName): ?string
    {
        foreach ($headers as $name => $values) {
            if (strtolower($name) !== $targetName || $values === []) {
                continue;
            }

            $value = preg_replace('/[\x00-\x1F\x7F]/', ' ', $values[0]);

            if ($value === null) {
                return null;
            }

            $value = trim(substr($value, 0, 200));

            return $value !== '' ? $value : null;
        }

        return null;
    }
}