<?php

declare(strict_types=1);

use SharadKashyap\ApiEndpointDiagnosticTool\Diagnosis\DiagnosisEngine;
use SharadKashyap\ApiEndpointDiagnosticTool\Http\CurlEndpointProbe;
use SharadKashyap\ApiEndpointDiagnosticTool\Http\EndpointRequest;
use SharadKashyap\ApiEndpointDiagnosticTool\Output\JsonReportFormatter;

require dirname(__DIR__) . '/vendor/autoload.php';

$url = $argv[1] ?? null;

if ($url === null) {
    fwrite(STDERR, "Usage: php bin/diagnose.php <endpoint-url> [--json]\n");
    exit(1);
}

try {
    $result = (new CurlEndpointProbe())->probe(new EndpointRequest(url: $url));
    $report = (new DiagnosisEngine())->diagnose($result);

    if (in_array('--json', $argv, true)) {
        echo (new JsonReportFormatter())->format($report), PHP_EOL;
        exit(0);
    }

    echo 'Request Status: ' . ($report->requestSuccessful ? 'Successful' : 'Failed') . PHP_EOL;
    echo 'Endpoint Reachable: ' . ($report->endpointReachable ? 'Yes' : 'No') . PHP_EOL;
    echo 'HTTP Status: ' . ($report->httpStatus ?? 'Unavailable') . PHP_EOL;
    echo 'Response Type: ' . $report->responseType . PHP_EOL;
    echo 'Likely Cause: ' . $report->likelyCause . PHP_EOL;
    echo 'Suggested Check: ' . $report->suggestedCheck . PHP_EOL;
    echo 'Duration: ' . $report->durationMilliseconds . ' ms' . PHP_EOL;
} catch (Throwable $exception) {
    fwrite(STDERR, 'Diagnostic failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

