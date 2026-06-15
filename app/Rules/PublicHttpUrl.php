<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rejects URLs that could be used to reach internal/private services (SSRF),
 * since this value is later used as the base URL for server-side HTTP requests.
 */
class PublicHttpUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $parts = parse_url((string) $value);

        if (! isset($parts['scheme'], $parts['host']) || ! in_array($parts['scheme'], ['http', 'https'], true)) {
            $fail('The :attribute must be a valid http or https URL.');

            return;
        }

        $host = $parts['host'];

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if (! filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                $fail('The :attribute must not point to a private or reserved network address.');
            }

            return;
        }

        if ($host === 'localhost' || ! str_contains($host, '.')) {
            $fail('The :attribute must be a publicly resolvable hostname.');
        }
    }
}
