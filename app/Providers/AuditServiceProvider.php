<?php

namespace App\Providers;

use App\Contracts\AuditEngine;
use App\Services\Audit\AuditOrchestrator;
use App\Services\Audit\AuditHistoryService;
use App\Services\Audit\Reporting\ReportGenerator;
use App\Services\Audit\Security\SecurityAuditEngine;
use App\Services\Audit\Security\Scanners\AuthenticationScanner;
use App\Services\Audit\Security\Scanners\AuthorizationScanner;
use App\Services\Audit\Security\Scanners\InputValidationScanner;
use App\Services\Audit\Security\Scanners\CsrfXssScanner;
use App\Services\Audit\Security\Scanners\SensitiveDataScanner;
use App\Services\Audit\Security\Scanners\DependencyScanner;
use App\Services\Audit\Security\Scanners\ConfigurationScanner;
use App\Services\Audit\Performance\PerformanceAuditEngine;
use App\Services\Audit\Performance\Analyzers\QueryAnalyzer;
use App\Services\Audit\Performance\Analyzers\CacheAnalyzer;
use App\Services\Audit\Performance\Analyzers\ApiResponseAnalyzer;
use App\Services\Audit\Performance\Analyzers\MemoryAnalyzer;
use App\Services\Audit\Database\DatabaseAuditEngine;
use App\Services\Audit\Database\Analyzers\IndexAnalyzer;
use App\Services\Audit\Database\Analyzers\QueryOptimizer;
use App\Services\Audit\Database\Analyzers\TransactionAnalyzer;
use App\Services\Audit\Database\Analyzers\ConnectionAnalyzer;
use App\Services\Audit\CodeQuality\CodeQualityEngine;
use App\Services\Audit\CodeQuality\Analyzers\StyleChecker;
use App\Services\Audit\CodeQuality\Analyzers\ComplexityAnalyzer;
use App\Services\Audit\CodeQuality\Analyzers\TestCoverageAnalyzer;
use App\Services\Audit\CodeQuality\Analyzers\DocumentationAnalyzer;
use Illuminate\Support\ServiceProvider;

/**
 * AuditServiceProvider
 * 
 * Registers all audit system services and their dependencies in the
 * Laravel service container for dependency injection.
 * 
 * Engines registered:
 * - SecurityAuditEngine (7 scanners)
 * - PerformanceAuditEngine (4 analyzers)
 * - DatabaseAuditEngine (4 analyzers)
 * - CodeQualityEngine (4 analyzers)
 * 
 * @package App\Providers
 */
class AuditServiceProvider extends ServiceProvider
{
    /**
     * Register audit system services
     */
    public function register(): void
    {
        $this->mergeConfigFrom(base_path('config/audit.php'), 'audit');

        $this->registerSecurityEngine();
        $this->registerPerformanceEngine();
        $this->registerDatabaseEngine();
        $this->registerCodeQualityEngine();
        $this->registerOrchestrator();
        $this->registerReportGenerator();
        $this->registerHistoryService();
    }

    /**
     * Register SecurityAuditEngine with all scanners
     */
    private function registerSecurityEngine(): void
    {
        $this->app->singleton(SecurityAuditEngine::class, function ($app) {
            $engine = new SecurityAuditEngine();

            $scanners = config('audit.security.scanners', []);

            if ($scanners['authentication'] ?? true) $engine->addScanner(new AuthenticationScanner());
            if ($scanners['authorization'] ?? true) $engine->addScanner(new AuthorizationScanner());
            if ($scanners['input_validation'] ?? true) $engine->addScanner(new InputValidationScanner());
            if ($scanners['csrf_xss'] ?? true) $engine->addScanner(new CsrfXssScanner());
            if ($scanners['sensitive_data'] ?? true) $engine->addScanner(new SensitiveDataScanner());
            if ($scanners['dependency'] ?? true) $engine->addScanner(new DependencyScanner());
            if ($scanners['configuration'] ?? true) $engine->addScanner(new ConfigurationScanner());

            return $engine;
        });
    }

    /**
     * Register PerformanceAuditEngine with all analyzers
     */
    private function registerPerformanceEngine(): void
    {
        $this->app->singleton(PerformanceAuditEngine::class, function ($app) {
            $engine = new PerformanceAuditEngine();

            $analyzers = config('audit.performance.analyzers', []);

            if ($analyzers['query'] ?? true) $engine->addAnalyzer(new QueryAnalyzer());
            if ($analyzers['cache'] ?? true) $engine->addAnalyzer(new CacheAnalyzer());
            if ($analyzers['api_response'] ?? true) $engine->addAnalyzer(new ApiResponseAnalyzer());
            if ($analyzers['memory'] ?? true) $engine->addAnalyzer(new MemoryAnalyzer());

            return $engine;
        });
    }

    /**
     * Register DatabaseAuditEngine with all analyzers
     */
    private function registerDatabaseEngine(): void
    {
        $this->app->singleton(DatabaseAuditEngine::class, function ($app) {
            $engine = new DatabaseAuditEngine();

            $analyzers = config('audit.database.analyzers', []);

            if ($analyzers['index'] ?? true) $engine->addAnalyzer(new IndexAnalyzer());
            if ($analyzers['query_optimizer'] ?? true) $engine->addAnalyzer(new QueryOptimizer());
            if ($analyzers['transaction'] ?? true) $engine->addAnalyzer(new TransactionAnalyzer());
            if ($analyzers['connection'] ?? true) $engine->addAnalyzer(new ConnectionAnalyzer());

            return $engine;
        });
    }

    /**
     * Register CodeQualityEngine with all analyzers
     */
    private function registerCodeQualityEngine(): void
    {
        $this->app->singleton(CodeQualityEngine::class, function ($app) {
            $engine = new CodeQualityEngine();

            $analyzers = config('audit.code_quality.analyzers', []);

            if ($analyzers['style'] ?? true) $engine->addAnalyzer(new StyleChecker());
            if ($analyzers['complexity'] ?? true) $engine->addAnalyzer(new ComplexityAnalyzer());
            if ($analyzers['test_coverage'] ?? true) $engine->addAnalyzer(new TestCoverageAnalyzer());
            if ($analyzers['documentation'] ?? true) $engine->addAnalyzer(new DocumentationAnalyzer());

            return $engine;
        });
    }

    /**
     * Register AuditOrchestrator with all enabled engines
     */
    private function registerOrchestrator(): void
    {
        $this->app->singleton(AuditOrchestrator::class, function ($app) {
            $orchestrator = new AuditOrchestrator();
            $engines = config('audit.engines', []);

            if ($engines['security'] ?? true) {
                $orchestrator->setSecurityEngine($app->make(SecurityAuditEngine::class));
            }
            if ($engines['performance'] ?? true) {
                $orchestrator->setPerformanceEngine($app->make(PerformanceAuditEngine::class));
            }
            if ($engines['database'] ?? true) {
                $orchestrator->setDatabaseEngine($app->make(DatabaseAuditEngine::class));
            }
            if ($engines['code_quality'] ?? true) {
                $orchestrator->setCodeQualityEngine($app->make(CodeQualityEngine::class));
            }

            return $orchestrator;
        });
    }

    /**
     * Register ReportGenerator
     */
    private function registerReportGenerator(): void
    {
        $this->app->singleton(ReportGenerator::class, function ($app) {
            return new ReportGenerator();
        });
    }

    /**
     * Register AuditHistoryService
     */
    private function registerHistoryService(): void
    {
        $this->app->singleton(AuditHistoryService::class, function ($app) {
            return new AuditHistoryService(
                $app->make(ReportGenerator::class),
            );
        });
    }

    /**
     * Bootstrap audit system services
     */
    public function boot(): void
    {
        //
    }
}
