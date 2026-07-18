<?php

declare(strict_types=1);

namespace SharadKashyap\ApiEndpointDiagnosticTool\Output;

final class SensitiveDataRedactor
{
    public function redact(string $value): string
    {
        $patterns = [
            '/(https?:\/\/)[^\/\s:@]+:[^@\s\/]+@/i',
            '/([?&](?:api[_-]?key|access[_-]?token|token|secret|password)=)[^&\s]+/i',
            '/((?:api[_-]?key|access[_-]?token|secret|password)\s*[:=]\s*)[^\s,;]+/i',
            '/((?:Authorization\s*:\s*)?(?:Bearer|Basic)\s+)[A-Za-z0-9._~+\/=-]{12,}/i',
        ];

        $replacements = [
            '$1[REDACTED]@',
            '$1[REDACTED]',
            '$1[REDACTED]',
            '$1[REDACTED]',
        ];

        return preg_replace($patterns, $replacements, $value) ?? $value;
    }
}