<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * PerformanceMonitor Middleware
 * 
 * Tracks request performance metrics including execution time,
 * database query count, and memory usage. Logs slow requests
 * and adds performance headers to responses for debugging.
 * 
 * @package App\Http\Middleware
 */
class PerformanceMonitor
{
    /**
     * Threshold in milliseconds for slow request warning
     */
    private const SLOW_REQUEST_MS = 1000;

    /**
     * Threshold for excessive query count warning
     */
    private const MAX_QUERY_COUNT = 20;

    /**
     * Threshold in MB for high memory usage warning
     */
    private const HIGH_MEMORY_MB = 64;

    /**
     * Handle an incoming request
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip monitoring for audit and health check endpoints
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        // Enable query logging
        DB::enableQueryLog();

        /** @var Response $response */
        $response = $next($request);

        // Collect metrics
        $executionTimeMs = round((microtime(true) - $startTime) * 1000, 2);
        $memoryUsedBytes = memory_get_usage(true) - $startMemory;
        $memoryUsedMb = round($memoryUsedBytes / 1024 / 1024, 2);
        $peakMemoryMb = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        // Calculate total query time
        $totalQueryTimeMs = round(
            collect($queries)->sum('time'),
            2
        );

        // Disable query logging after collecting
        DB::disableQueryLog();

        // Add performance headers (only in non-production or when debug)
        if (config('app.debug') || config('audit.performance.headers_enabled', false)) {
            $response->headers->set('X-Request-Time-Ms', (string) $executionTimeMs);
            $response->headers->set('X-Query-Count', (string) $queryCount);
            $response->headers->set('X-Query-Time-Ms', (string) $totalQueryTimeMs);
            $response->headers->set('X-Memory-Used-Mb', (string) $memoryUsedMb);
            $response->headers->set('X-Memory-Peak-Mb', (string) $peakMemoryMb);
        }

        // Log performance data
        $this->logPerformance($request, [
            'execution_time_ms' => $executionTimeMs,
            'query_count' => $queryCount,
            'total_query_time_ms' => $totalQueryTimeMs,
            'memory_used_mb' => $memoryUsedMb,
            'peak_memory_mb' => $peakMemoryMb,
            'status_code' => $response->getStatusCode(),
        ]);

        return $response;
    }

    /**
     * Log performance metrics with appropriate severity
     *
     * @param Request $request
     * @param array<string, mixed> $metrics
     * @return void
     */
    private function logPerformance(Request $request, array $metrics): void
    {
        $context = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route' => $request->route()?->getName() ?? $request->path(),
            ...$metrics,
        ];

        // Critical: extremely slow request
        if ($metrics['execution_time_ms'] > self::SLOW_REQUEST_MS * 3) {
            Log::critical('CRITICAL: Extremely slow request detected', $context);
            return;
        }

        // Warning: slow request
        if ($metrics['execution_time_ms'] > self::SLOW_REQUEST_MS) {
            Log::warning('Slow request detected', $context);
            return;
        }

        // Warning: excessive queries (possible N+1)
        if ($metrics['query_count'] > self::MAX_QUERY_COUNT) {
            Log::warning('Excessive query count detected (possible N+1)', $context);
            return;
        }

        // Warning: high memory usage
        if ($metrics['memory_used_mb'] > self::HIGH_MEMORY_MB) {
            Log::warning('High memory usage detected', $context);
            return;
        }

        // Debug: normal performance log
        Log::debug('Request performance', $context);
    }

    /**
     * Check if the request should skip monitoring
     *
     * @param Request $request
     * @return bool
     */
    private function shouldSkip(Request $request): bool
    {
        $skipPaths = [
            'up',
            '_debugbar',
            'telescope',
        ];

        foreach ($skipPaths as $path) {
            if ($request->is($path . '*')) {
                return true;
            }
        }

        return false;
    }
}
