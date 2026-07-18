<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Http;

interface EndpointProbe
{
    public function probe(EndpointRequest $request): EndpointResult;
}