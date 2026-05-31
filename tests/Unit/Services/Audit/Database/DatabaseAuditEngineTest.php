<?php

namespace Tests\Unit\Services\Audit\Database;

use App\DataModels\Audit\AuditResult;
use App\Services\Audit\Database\DatabaseAuditEngine;
use App\Services\Audit\Database\Analyzers\IndexAnalyzer;
use App\Services\Audit\Database\Analyzers\QueryOptimizer;
use App\Services\Audit\Database\Analyzers\TransactionAnalyzer;
use App\Services\Audit\Database\Analyzers\ConnectionAnalyzer;
use Tests\TestCase;

/**
 * Test suite for DatabaseAuditEngine and its analyzers
 */
class DatabaseAuditEngineTest extends TestCase
{
    private string $testBasePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testBasePath = sys_get_temp_dir() . '/audit_test_db_' . uniqid();
        mkdir($this->testBasePath, 0755, true);
        mkdir($this->testBasePath . '/database/migrations', 0755, true);
        mkdir($this->testBasePath . '/app/Http/Controllers', 0755, true);
        mkdir($this->testBasePath . '/app/Services', 0755, true);
        mkdir($this->testBasePath . '/app/Models', 0755, true);
        mkdir($this->testBasePath . '/config', 0755, true);

        // Create a fake .env
        file_put_contents($this->testBasePath . '/.env', "APP_ENV=local\nDB_CONNECTION=mysql\nDB_USERNAME=root\nDB_PASSWORD=password\n");
    }

    protected function tearDown(): void
    {
        $this->deleteDir($this->testBasePath);
        parent::tearDown();
    }

    /**
     * Test that engine can run with no analyzers
     */
    public function test_engine_runs_with_no_analyzers(): void
    {
        $engine = new DatabaseAuditEngine();
        $results = $engine->analyze();

        $this->assertIsArray($results);
        $this->assertCount(0, $results);
    }

    /**
     * Test that engine generates a report
     */
    public function test_engine_generates_report(): void
    {
        $engine = new DatabaseAuditEngine();
        $report = $engine->generateReport();

        $this->assertNotNull($report);
        $this->assertStringStartsWith('audit_', $report->getId());
    }

    /**
     * Test IndexAnalyzer detects foreign keys without indexes
     */
    public function test_index_analyzer_detects_missing_fk_index(): void
    {
        $migration = <<<'PHP'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreignId('project_id');
            $table->timestamps();
        });
    }
};
PHP;

        file_put_contents(
            $this->testBasePath . '/database/migrations/2024_01_01_create_orders_table.php',
            $migration
        );

        $analyzer = new IndexAnalyzer($this->testBasePath);
        $results = $analyzer->scan();

        $this->assertNotEmpty($results);

        $messages = array_map(fn(AuditResult $r) => $r->getMessage(), $results);
        $hasUserIdFinding = false;
        foreach ($messages as $msg) {
            if (str_contains($msg, 'user_id') || str_contains($msg, 'project_id')) {
                $hasUserIdFinding = true;
            }
        }
        $this->assertTrue($hasUserIdFinding, 'Should detect missing FK index');
    }

    /**
     * Test IndexAnalyzer skips constrained foreign keys
     */
    public function test_index_analyzer_skips_constrained_fk(): void
    {
        $migration = <<<'PHP'
<?php
return new class extends \Illuminate\Database\Migrations\Migration {
    public function up(): void {
        \Illuminate\Support\Facades\Schema::create('orders', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
    }
};
PHP;

        file_put_contents(
            $this->testBasePath . '/database/migrations/2024_01_01_create_orders_table.php',
            $migration
        );

        $analyzer = new IndexAnalyzer($this->testBasePath);
        $results = $analyzer->scan();

        // Should NOT flag user_id since it uses constrained()
        $fkFindings = array_filter(
            $results,
            fn(AuditResult $r) => str_contains($r->getMessage(), 'user_id')
        );

        $this->assertCount(0, $fkFindings, 'Should not flag constrained FK');
    }

    /**
     * Test QueryOptimizer detects raw SQL with concatenation
     */
    public function test_query_optimizer_detects_raw_sql_injection(): void
    {
        $controller = <<<'PHP'
<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class TestController {
    public function index() {
        $id = request('id');
        $results = DB::select("SELECT * FROM users WHERE id = " . $id);
        return $results;
    }
}
PHP;

        file_put_contents(
            $this->testBasePath . '/app/Http/Controllers/TestController.php',
            $controller
        );

        $analyzer = new QueryOptimizer($this->testBasePath);
        $results = $analyzer->scan();

        $sqlInjection = array_filter(
            $results,
            fn(AuditResult $r) => $r->getSeverity() === 'critical'
        );

        $this->assertNotEmpty($sqlInjection, 'Should detect SQL injection risk');
    }

    /**
     * Test TransactionAnalyzer detects missing transactions
     */
    public function test_transaction_analyzer_detects_missing_transactions(): void
    {
        $service = <<<'PHP'
<?php
namespace App\Services;

class OrderService {
    public function createOrder($data) {
        $order = Order::create($data);
        $order->items()->create($data['items']);
        $order->payment()->create($data['payment']);
        return $order;
    }
}
PHP;

        file_put_contents(
            $this->testBasePath . '/app/Services/OrderService.php',
            $service
        );

        $analyzer = new TransactionAnalyzer($this->testBasePath);
        $results = $analyzer->scan();

        $transactionFindings = array_filter(
            $results,
            fn(AuditResult $r) => $r->getCategory() === 'transaction_management'
        );

        $this->assertNotEmpty($transactionFindings, 'Should detect missing transactions');
    }

    /**
     * Test ConnectionAnalyzer detects root user
     */
    public function test_connection_analyzer_detects_root_user(): void
    {
        $analyzer = new ConnectionAnalyzer($this->testBasePath);
        $results = $analyzer->scan();

        $rootFindings = array_filter(
            $results,
            fn(AuditResult $r) => str_contains($r->getMessage(), 'root user')
        );

        $this->assertNotEmpty($rootFindings, 'Should detect root user usage');
    }

    /**
     * Test ConnectionAnalyzer detects default password
     */
    public function test_connection_analyzer_detects_default_password(): void
    {
        $analyzer = new ConnectionAnalyzer($this->testBasePath);
        $results = $analyzer->scan();

        $passwordFindings = array_filter(
            $results,
            fn(AuditResult $r) => str_contains($r->getMessage(), 'password')
        );

        $this->assertNotEmpty($passwordFindings, 'Should detect default password');
    }

    /**
     * Test full engine with all analyzers
     */
    public function test_full_engine_with_analyzers(): void
    {
        // Create test files that will trigger findings
        $migration = <<<'PHP'
<?php
return new class extends \Illuminate\Database\Migrations\Migration {
    public function up(): void {
        \Illuminate\Support\Facades\Schema::create('orders', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->timestamps();
        });
    }
};
PHP;
        file_put_contents(
            $this->testBasePath . '/database/migrations/2024_01_01_create_orders_table.php',
            $migration
        );

        $engine = new DatabaseAuditEngine();
        $engine->addAnalyzer(new IndexAnalyzer($this->testBasePath));
        $engine->addAnalyzer(new QueryOptimizer($this->testBasePath));
        $engine->addAnalyzer(new TransactionAnalyzer($this->testBasePath));
        $engine->addAnalyzer(new ConnectionAnalyzer($this->testBasePath));

        $report = $engine->generateReport();

        $this->assertGreaterThan(0, $report->getTotalFindings());
        $this->assertNotEmpty($report->getRecommendations());
    }

    /**
     * Recursively delete directory
     */
    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) return;

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($dir);
    }
}
