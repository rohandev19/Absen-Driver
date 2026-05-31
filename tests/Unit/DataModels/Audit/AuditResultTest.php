<?php

namespace Tests\Unit\DataModels\Audit;

use App\DataModels\Audit\AuditResult;
use DateTime;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Test suite for AuditResult data model
 */
class AuditResultTest extends TestCase
{
    /**
     * Test that AuditResult can be created with valid data
     */
    public function test_can_create_audit_result_with_valid_data(): void
    {
        $timestamp = new DateTime();
        $result = new AuditResult(
            type: 'security',
            severity: 'high',
            category: 'authentication',
            message: 'Missing rate limiting on login endpoint',
            details: ['endpoint' => '/api/login', 'method' => 'POST'],
            timestamp: $timestamp
        );

        $this->assertEquals('security', $result->getType());
        $this->assertEquals('high', $result->getSeverity());
        $this->assertEquals('authentication', $result->getCategory());
        $this->assertEquals('Missing rate limiting on login endpoint', $result->getMessage());
        $this->assertEquals(['endpoint' => '/api/login', 'method' => 'POST'], $result->getDetails());
        $this->assertEquals($timestamp, $result->getTimestamp());
    }

    /**
     * Test that invalid type throws exception
     */
    public function test_invalid_type_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid type 'invalid'");

        new AuditResult(
            type: 'invalid',
            severity: 'high',
            category: 'authentication',
            message: 'Test message'
        );
    }

    /**
     * Test that invalid severity throws exception
     */
    public function test_invalid_severity_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid severity 'invalid'");

        new AuditResult(
            type: 'security',
            severity: 'invalid',
            category: 'authentication',
            message: 'Test message'
        );
    }

    /**
     * Test that invalid category throws exception
     */
    public function test_invalid_category_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid category 'invalid'");

        new AuditResult(
            type: 'security',
            severity: 'high',
            category: 'invalid',
            message: 'Test message'
        );
    }

    /**
     * Test that empty message throws exception
     */
    public function test_empty_message_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Message cannot be empty');

        new AuditResult(
            type: 'security',
            severity: 'high',
            category: 'authentication',
            message: ''
        );
    }

    /**
     * Test isCritical method
     */
    public function test_is_critical_method(): void
    {
        $critical = new AuditResult(
            type: 'security',
            severity: 'critical',
            category: 'authentication',
            message: 'Critical issue'
        );

        $high = new AuditResult(
            type: 'security',
            severity: 'high',
            category: 'authentication',
            message: 'High issue'
        );

        $this->assertTrue($critical->isCritical());
        $this->assertFalse($high->isCritical());
    }

    /**
     * Test isHigh method
     */
    public function test_is_high_method(): void
    {
        $high = new AuditResult(
            type: 'security',
            severity: 'high',
            category: 'authentication',
            message: 'High issue'
        );

        $medium = new AuditResult(
            type: 'security',
            severity: 'medium',
            category: 'authentication',
            message: 'Medium issue'
        );

        $this->assertTrue($high->isHigh());
        $this->assertFalse($medium->isHigh());
    }

    /**
     * Test getSeverityWeight method
     */
    public function test_get_severity_weight(): void
    {
        $critical = new AuditResult(
            type: 'security',
            severity: 'critical',
            category: 'authentication',
            message: 'Critical'
        );

        $high = new AuditResult(
            type: 'security',
            severity: 'high',
            category: 'authentication',
            message: 'High'
        );

        $medium = new AuditResult(
            type: 'security',
            severity: 'medium',
            category: 'authentication',
            message: 'Medium'
        );

        $this->assertEquals(5, $critical->getSeverityWeight());
        $this->assertEquals(4, $high->getSeverityWeight());
        $this->assertEquals(3, $medium->getSeverityWeight());
    }

    /**
     * Test toArray method
     */
    public function test_to_array(): void
    {
        $timestamp = new DateTime('2024-01-15 10:30:00');
        $result = new AuditResult(
            type: 'security',
            severity: 'high',
            category: 'authentication',
            message: 'Test message',
            details: ['key' => 'value'],
            timestamp: $timestamp
        );

        $array = $result->toArray();

        $this->assertEquals('security', $array['type']);
        $this->assertEquals('high', $array['severity']);
        $this->assertEquals('authentication', $array['category']);
        $this->assertEquals('Test message', $array['message']);
        $this->assertEquals(['key' => 'value'], $array['details']);
        $this->assertEquals('2024-01-15 10:30:00', $array['timestamp']);
    }

    /**
     * Test fromArray method
     */
    public function test_from_array(): void
    {
        $data = [
            'type' => 'performance',
            'severity' => 'medium',
            'category' => 'query_optimization',
            'message' => 'N+1 query detected',
            'details' => ['file' => 'UserController.php'],
            'timestamp' => '2024-01-15 10:30:00'
        ];

        $result = AuditResult::fromArray($data);

        $this->assertEquals('performance', $result->getType());
        $this->assertEquals('medium', $result->getSeverity());
        $this->assertEquals('query_optimization', $result->getCategory());
        $this->assertEquals('N+1 query detected', $result->getMessage());
        $this->assertEquals(['file' => 'UserController.php'], $result->getDetails());
    }
}
