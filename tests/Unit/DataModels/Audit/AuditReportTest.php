<?php

namespace Tests\Unit\DataModels\Audit;

use App\DataModels\Audit\AuditReport;
use App\DataModels\Audit\AuditResult;
use DateTime;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Test suite for AuditReport data model
 */
class AuditReportTest extends TestCase
{
    /**
     * Test that AuditReport can be created with valid data
     */
    public function test_can_create_audit_report_with_valid_data(): void
    {
        $timestamp = new DateTime();
        $report = new AuditReport(
            id: 'audit_20240115_103000_abc123',
            timestamp: $timestamp,
            results: [],
            summary: [],
            recommendations: []
        );

        $this->assertEquals('audit_20240115_103000_abc123', $report->getId());
        $this->assertEquals($timestamp, $report->getTimestamp());
        $this->assertIsArray($report->getResults());
        $this->assertIsArray($report->getSummary());
        $this->assertIsArray($report->getRecommendations());
    }

    /**
     * Test that empty ID throws exception
     */
    public function test_empty_id_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Report ID cannot be empty');

        new AuditReport(
            id: '',
            timestamp: new DateTime()
        );
    }

    /**
     * Test adding a single result
     */
    public function test_add_result(): void
    {
        $report = new AuditReport(
            id: 'test_report',
            timestamp: new DateTime()
        );

        $result = new AuditResult(
            type: 'security',
            severity: 'high',
            category: 'authentication',
            message: 'Test finding'
        );

        $report->addResult($result);

        $this->assertCount(1, $report->getResults());
        $this->assertEquals(1, $report->getTotalFindings());
    }

    /**
     * Test adding multiple results
     */
    public function test_add_results(): void
    {
        $report = new AuditReport(
            id: 'test_report',
            timestamp: new DateTime()
        );

        $results = [
            new AuditResult(
                type: 'security',
                severity: 'critical',
                category: 'authentication',
                message: 'Critical finding'
            ),
            new AuditResult(
                type: 'performance',
                severity: 'high',
                category: 'query_optimization',
                message: 'Performance issue'
            ),
        ];

        $report->addResults($results);

        $this->assertCount(2, $report->getResults());
        $this->assertEquals(2, $report->getTotalFindings());
    }

    /**
     * Test summary generation
     */
    public function test_summary_generation(): void
    {
        $report = new AuditReport(
            id: 'test_report',
            timestamp: new DateTime()
        );

        $report->addResults([
            new AuditResult(
                type: 'security',
                severity: 'critical',
                category: 'authentication',
                message: 'Critical'
            ),
            new AuditResult(
                type: 'security',
                severity: 'high',
                category: 'authorization',
                message: 'High'
            ),
            new AuditResult(
                type: 'performance',
                severity: 'medium',
                category: 'query_optimization',
                message: 'Medium'
            ),
        ]);

        $summary = $report->getSummary();

        $this->assertEquals(3, $summary['total_findings']);
        $this->assertEquals(1, $summary['by_severity']['critical']);
        $this->assertEquals(1, $summary['by_severity']['high']);
        $this->assertEquals(1, $summary['by_severity']['medium']);
        $this->assertEquals(2, $summary['by_type']['security']);
        $this->assertEquals(1, $summary['by_type']['performance']);
    }

    /**
     * Test filtering by severity
     */
    public function test_get_results_by_severity(): void
    {
        $report = new AuditReport(
            id: 'test_report',
            timestamp: new DateTime()
        );

        $report->addResults([
            new AuditResult(
                type: 'security',
                severity: 'critical',
                category: 'authentication',
                message: 'Critical'
            ),
            new AuditResult(
                type: 'security',
                severity: 'high',
                category: 'authorization',
                message: 'High'
            ),
            new AuditResult(
                type: 'performance',
                severity: 'critical',
                category: 'query_optimization',
                message: 'Critical 2'
            ),
        ]);

        $criticalResults = $report->getResultsBySeverity('critical');

        $this->assertCount(2, $criticalResults);
    }

    /**
     * Test filtering by type
     */
    public function test_get_results_by_type(): void
    {
        $report = new AuditReport(
            id: 'test_report',
            timestamp: new DateTime()
        );

        $report->addResults([
            new AuditResult(
                type: 'security',
                severity: 'critical',
                category: 'authentication',
                message: 'Security 1'
            ),
            new AuditResult(
                type: 'security',
                severity: 'high',
                category: 'authorization',
                message: 'Security 2'
            ),
            new AuditResult(
                type: 'performance',
                severity: 'medium',
                category: 'query_optimization',
                message: 'Performance'
            ),
        ]);

        $securityResults = $report->getResultsByType('security');

        $this->assertCount(2, $securityResults);
    }

    /**
     * Test filtering by category
     */
    public function test_get_results_by_category(): void
    {
        $report = new AuditReport(
            id: 'test_report',
            timestamp: new DateTime()
        );

        $report->addResults([
            new AuditResult(
                type: 'security',
                severity: 'critical',
                category: 'authentication',
                message: 'Auth 1'
            ),
            new AuditResult(
                type: 'security',
                severity: 'high',
                category: 'authentication',
                message: 'Auth 2'
            ),
            new AuditResult(
                type: 'security',
                severity: 'medium',
                category: 'authorization',
                message: 'Authz'
            ),
        ]);

        $authResults = $report->getResultsByCategory('authentication');

        $this->assertCount(2, $authResults);
    }

    /**
     * Test getting critical and high findings
     */
    public function test_get_critical_and_high_findings(): void
    {
        $report = new AuditReport(
            id: 'test_report',
            timestamp: new DateTime()
        );

        $report->addResults([
            new AuditResult(
                type: 'security',
                severity: 'critical',
                category: 'authentication',
                message: 'Critical'
            ),
            new AuditResult(
                type: 'security',
                severity: 'high',
                category: 'authorization',
                message: 'High'
            ),
            new AuditResult(
                type: 'performance',
                severity: 'medium',
                category: 'query_optimization',
                message: 'Medium'
            ),
            new AuditResult(
                type: 'performance',
                severity: 'low',
                category: 'caching',
                message: 'Low'
            ),
        ]);

        $criticalAndHigh = $report->getCriticalAndHighFindings();

        $this->assertCount(2, $criticalAndHigh);
    }

    /**
     * Test hasCriticalFindings method
     */
    public function test_has_critical_findings(): void
    {
        $report = new AuditReport(
            id: 'test_report',
            timestamp: new DateTime()
        );

        $this->assertFalse($report->hasCriticalFindings());

        $report->addResult(new AuditResult(
            type: 'security',
            severity: 'critical',
            category: 'authentication',
            message: 'Critical'
        ));

        $this->assertTrue($report->hasCriticalFindings());
    }

    /**
     * Test hasHighFindings method
     */
    public function test_has_high_findings(): void
    {
        $report = new AuditReport(
            id: 'test_report',
            timestamp: new DateTime()
        );

        $this->assertFalse($report->hasHighFindings());

        $report->addResult(new AuditResult(
            type: 'security',
            severity: 'high',
            category: 'authentication',
            message: 'High'
        ));

        $this->assertTrue($report->hasHighFindings());
    }

    /**
     * Test sorting by severity
     */
    public function test_get_results_sorted_by_severity(): void
    {
        $report = new AuditReport(
            id: 'test_report',
            timestamp: new DateTime()
        );

        $report->addResults([
            new AuditResult(
                type: 'security',
                severity: 'low',
                category: 'authentication',
                message: 'Low'
            ),
            new AuditResult(
                type: 'security',
                severity: 'critical',
                category: 'authorization',
                message: 'Critical'
            ),
            new AuditResult(
                type: 'performance',
                severity: 'medium',
                category: 'query_optimization',
                message: 'Medium'
            ),
        ]);

        $sorted = $report->getResultsSortedBySeverity();

        $this->assertEquals('critical', $sorted[0]->getSeverity());
        $this->assertEquals('medium', $sorted[1]->getSeverity());
        $this->assertEquals('low', $sorted[2]->getSeverity());
    }

    /**
     * Test recommendations
     */
    public function test_recommendations(): void
    {
        $report = new AuditReport(
            id: 'test_report',
            timestamp: new DateTime()
        );

        $recommendations = [
            'Implement rate limiting on authentication endpoints',
            'Add missing indexes to database tables',
            'Enable query caching for dashboard statistics',
        ];

        $report->setRecommendations($recommendations);

        $this->assertCount(3, $report->getRecommendations());
        $this->assertEquals($recommendations, $report->getRecommendations());
    }

    /**
     * Test adding single recommendation
     */
    public function test_add_recommendation(): void
    {
        $report = new AuditReport(
            id: 'test_report',
            timestamp: new DateTime()
        );

        $report->addRecommendation('Fix critical security issue');
        $report->addRecommendation('Optimize slow queries');

        $this->assertCount(2, $report->getRecommendations());
    }

    /**
     * Test toArray method
     */
    public function test_to_array(): void
    {
        $timestamp = new DateTime('2024-01-15 10:30:00');
        $report = new AuditReport(
            id: 'test_report',
            timestamp: $timestamp
        );

        $report->addResult(new AuditResult(
            type: 'security',
            severity: 'high',
            category: 'authentication',
            message: 'Test'
        ));

        $report->addRecommendation('Fix this issue');

        $array = $report->toArray();

        $this->assertEquals('test_report', $array['id']);
        $this->assertEquals('2024-01-15 10:30:00', $array['timestamp']);
        $this->assertIsArray($array['results']);
        $this->assertCount(1, $array['results']);
        $this->assertIsArray($array['summary']);
        $this->assertIsArray($array['recommendations']);
        $this->assertCount(1, $array['recommendations']);
    }

    /**
     * Test generateId method
     */
    public function test_generate_id(): void
    {
        $id = AuditReport::generateId();

        $this->assertStringStartsWith('audit_', $id);
        $this->assertMatchesRegularExpression('/^audit_\d{8}_\d{6}_[a-f0-9]{8}$/', $id);
    }
}
