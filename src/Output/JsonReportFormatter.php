<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Output;

use JsonException;
use SharadKashyap\ApiEndpointDiagnosticTool\Diagnosis\DiagnosticReport;

final class JsonReportFormatter
{
    public function __construct(
        private readonly SensitiveDataRedactor $redactor = new SensitiveDataRedactor(),
    ) {
    }

    /**
     * @throws JsonException
     */
    public function format(DiagnosticReport $report): string
    {
        $data = $report->toArray();

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = $this->redactor->redact($value);
            }
        }

        return json_encode(
            $data,
            JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
        );
    }
}