<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Output;

use JsonException;
use SharadKashyap\ApiEndpointDiagnosticTool\Diagnosis\DiagnosticReport;

final class JsonReportFormatter
{
    /**
     * @throws JsonException
     */
    public function format(DiagnosticReport $report): string
    {
        return json_encode(
            $report->toArray(),
            JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
        );
    }
}