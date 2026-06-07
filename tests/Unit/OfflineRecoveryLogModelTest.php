<?php

namespace Tests\Unit;

use App\Models\Attendance;
use App\Models\Driver;
use App\Models\OfflineRecoveryLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflineRecoveryLogModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the model has all required fillable fields.
     *
     * @return void
     */
    public function test_model_has_all_fillable_fields(): void
    {
        $fillableFields = [
            'driver_id',
            'attendance_id',
            'offline_entry_id',
            'device_timestamp',
            'recovery_timestamp',
            'delay_minutes',
            'result',
            'error_code',
            'error_message',
            'retry_count',
            'photo_size_kb',
        ];

        $model = new OfflineRecoveryLog();
        
        foreach ($fillableFields as $field) {
            $this->assertContains(
                $field,
                $model->getFillable(),
                "Field '{$field}' should be in fillable array"
            );
        }
    }

    /**
     * Test that device_timestamp is cast to datetime.
     *
     * @return void
     */
    public function test_device_timestamp_is_cast_to_datetime(): void
    {
        $model = new OfflineRecoveryLog();
        $casts = $model->getCasts();

        $this->assertArrayHasKey('device_timestamp', $casts);
        $this->assertEquals('datetime', $casts['device_timestamp']);
    }

    /**
     * Test that recovery_timestamp is cast to datetime.
     *
     * @return void
     */
    public function test_recovery_timestamp_is_cast_to_datetime(): void
    {
        $model = new OfflineRecoveryLog();
        $casts = $model->getCasts();

        $this->assertArrayHasKey('recovery_timestamp', $casts);
        $this->assertEquals('datetime', $casts['recovery_timestamp']);
    }

    /**
     * Test that result is cast to string.
     *
     * @return void
     */
    public function test_result_is_cast_to_string(): void
    {
        $model = new OfflineRecoveryLog();
        $casts = $model->getCasts();

        $this->assertArrayHasKey('result', $casts);
        $this->assertEquals('string', $casts['result']);
    }

    /**
     * Test belongsTo Driver relationship.
     *
     * @return void
     */
    public function test_belongs_to_driver_relationship(): void
    {
        $model = new OfflineRecoveryLog();
        $relation = $model->driver();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(Driver::class, get_class($relation->getRelated()));
    }

    /**
     * Test belongsTo Attendance relationship.
     *
     * @return void
     */
    public function test_belongs_to_attendance_relationship(): void
    {
        $model = new OfflineRecoveryLog();
        $relation = $model->attendance();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(Attendance::class, get_class($relation->getRelated()));
    }
}
