<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * SlowQueryLogger Middleware
 * 
 * Logs individual database queries that exceed a configurable threshold.
 * Captures the query SQL, bindings, execution time, and calling context
 * for optimization analysis.
 * 
 * @package App\Http\Middleware
 */
class SlowQueryLogger
{
    /**
     * Default slow query threshold in milliseconds
     */
    private const DEFAULT_THRESHOLD_MS = 100;

    /**
     * Handle an incoming request
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $threshold = config('audit.performance.slow_query_threshold_ms', self::DEFAULT_THRESHOLD_MS);

        // Listen for slow queries
        DB::listen(function ($query) use ($threshold, $request) {
            if ($query->time >= $threshold) {
                $this->logSlowQuery($query, $request, $threshold);
            }
        });

        return $next($request);
    }

    /**
     * Log a slow query with context
     *
     * @param object $query The query event
     * @param Request $request The current request
     * @param float $threshold The threshold that was exceeded
     * @return void
     */
    private function logSlowQuery(object $query, Request $request, float $threshold): void
    {
        $severity = $this->getSeverity($query->time, $threshold);

        $context = [
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'time_ms' => round($query->time, 2),
            'threshold_ms' => $threshold,
            'connection' => $query->connectionName,
            'request_method' => $request->method(),
            'request_url' => $request->fullUrl(),
            'route' => $request->route()?->getName() ?? $request->path(),
        ];

        match ($severity) {
            'critical' => Log::critical('CRITICAL: Extremely slow query', $context),
            'warning' => Log::warning('Slow query detected', $context),
            default => Log::info('Query exceeded threshold', $context),
        };
    }

    /**
     * Determine log severity based on how much the threshold was exceeded
     *
     * @param float $timeMs Query execution time in ms
     * @param float $threshold Configured threshold
     * @return string 'critical', 'warning', or 'info'
     */
    private function getSeverity(float $timeMs, float $threshold): string
    {
        if ($timeMs >= $threshold * 10) {
            return 'critical'; // 10x threshold
        }

        if ($timeMs >= $threshold * 3) {
            return 'warning'; // 3x threshold
        }

        return 'info';
    }
}
