<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetFilamentMemoryLimit
{
    private const TARGET_LIMIT = '256M';

    private const TARGET_LIMIT_BYTES = 256 * 1024 * 1024;

    public function handle(Request $request, Closure $next): Response
    {
        $currentLimit = ini_get('memory_limit');

        if (
            is_string($currentLimit)
            && $this->memoryLimitInBytes($currentLimit) < self::TARGET_LIMIT_BYTES
        ) {
            ini_set('memory_limit', self::TARGET_LIMIT);
        }

        return $next($request);
    }

    private function memoryLimitInBytes(string $limit): int
    {
        $limit = trim($limit);

        if ($limit === '-1') {
            return PHP_INT_MAX;
        }

        if (! preg_match('/^(\d+)\s*([KMG]?)$/i', $limit, $matches)) {
            return PHP_INT_MAX;
        }

        $multiplier = match (strtolower($matches[2])) {
            'k' => 1024,
            'm' => 1024 ** 2,
            'g' => 1024 ** 3,
            default => 1,
        };

        return (int) $matches[1] * $multiplier;
    }
}
