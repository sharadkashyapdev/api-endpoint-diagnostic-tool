<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Diagnosis;

final class TransportFailureAnalyzer
{
    /**
     * @return array{likelyCause: string, suggestedCheck: string}
     */
    public function analyze(?string $transportError): array
    {
        $error = strtolower($transportError ?? '');

        if (
            str_contains($error, 'could not resolve host')
            || str_contains($error, 'name or service not known')
            || str_contains($error, 'getaddrinfo')
        ) {
            return [
                'likelyCause' => 'DNS resolution failed for the endpoint host.',
                'suggestedCheck' => 'Verify the hostname, DNS records, and local DNS resolver.',
            ];
        }

        if (str_contains($error, 'timed out') || str_contains($error, 'timeout')) {
            return [
                'likelyCause' => 'The endpoint did not respond before the request timed out.',
                'suggestedCheck' => 'Check server availability, network latency, and timeout configuration.',
            ];
        }

        if (str_contains($error, 'ssl') || str_contains($error, 'certificate')) {
            return [
                'likelyCause' => 'SSL/TLS validation or negotiation failed.',
                'suggestedCheck' => 'Verify the certificate chain, hostname, expiry date, and supported TLS versions.',
            ];
        }

        if (
            str_contains($error, 'connection refused')
            || str_contains($error, 'failed to connect')
            || str_contains($error, "couldn't connect")
        ) {
            return [
                'likelyCause' => 'The server refused or could not accept the connection.',
                'suggestedCheck' => 'Verify the host, port, firewall rules, and whether the API service is running.',
            ];
        }

        return [
            'likelyCause' => $transportError ?? 'Endpoint could not be reached.',
            'suggestedCheck' => 'Verify the URL, network connection, SSL certificate, and server availability.',
        ];
    }
}