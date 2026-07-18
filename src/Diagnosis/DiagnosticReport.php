<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Diagnosis;

final readonly class DiagnosticReport
{
    public function __construct(
        public bool $requestSuccessful,
        public bool $endpointReachable,
        public ?int $httpStatus,
        public string $responseType,
        public string $likelyCause,
        public string $suggestedCheck,
        public float $durationMilliseconds,
    ) {
    }

    /**
     * @return array<string, bool|int|float|string|null>
     */
    public function toArray(): array
    {
        return [
            'request_successful' => $this->requestSuccessful,
            'endpoint_reachable' => $this->endpointReachable,
            'http_status' => $this->httpStatus,
            'response_type' => $this->responseType,
            'likely_cause' => $this->likelyCause,
            'suggested_check' => $this->suggestedCheck,
            'duration_milliseconds' => $this->durationMilliseconds,
        ];
    }
}