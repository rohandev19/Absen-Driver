<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transport_costs', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('attendance_id')->unique();
            
            // Trip Details
            $table->date('trip_date');
            $table->string('do_number', 500)->comment('Comma-separated delivery order numbers');
            $table->unsignedInteger('drop_point_count');
            $table->text('delivery_location');
            
            // Odometer (Auto-filled from Attendance)
            $table->unsignedInteger('odometer_start')->comment('From attendance.speedo_awal');
            $table->unsignedInteger('odometer_end')->comment('From attendance.speedo_akhir');
            // odometer_difference will be calculated in model accessor
            
            // Cost Breakdown
            $table->decimal('gasoline_cost', 10, 2)->default(0.00);
            $table->decimal('toll_cost', 10, 2)->default(0.00);
            $table->decimal('parking_cost', 10, 2)->default(0.00);
            // total_cost will be calculated in model accessor
            
            // Fuel Efficiency
            $table->decimal('gasoline_price_per_liter', 8, 2)->nullable();
            $table->decimal('fuel_consumed', 8, 2)->nullable()->comment('Calculated: gasoline_cost / gasoline_price_per_liter');
            $table->decimal('fuel_efficiency_ratio', 8, 2)->nullable()->comment('KM per liter');
            
            // Delivery Time Tracking
            $table->dateTime('delivery_start_time');
            $table->dateTime('delivery_end_time');
            $table->decimal('actual_delivery_hours', 5, 2)->nullable()->comment('Hours between start and end');
            $table->decimal('overtime_hours', 5, 2)->default(0.00);
            $table->decimal('overtime_rate_per_hour', 10, 2)->nullable();
            $table->decimal('overtime_payment', 10, 2)->default(0.00);
            
            // Performance Bonus
            $table->decimal('bonus_driver', 10, 2)->default(0.00);
            $table->text('bonus_notes')->nullable()->comment('Breakdown of bonus calculation');
            
            // Approval Workflow
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Audit Trail
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['driver_id', 'trip_date'], 'idx_driver_date');
            $table->index('approval_status', 'idx_approval_status');
            $table->index(['project_id', 'trip_date'], 'idx_project_date');
            $table->index('created_at', 'idx_created_at');
            
            // Foreign Key Constraints
            $table->foreign('driver_id', 'fk_transport_costs_driver')
                  ->references('id')->on('drivers')
                  ->onDelete('restrict');
            
            $table->foreign('vehicle_id', 'fk_transport_costs_vehicle')
                  ->references('id')->on('vehicles')
                  ->onDelete('restrict');
            
            $table->foreign('project_id', 'fk_transport_costs_project')
                  ->references('id')->on('projects')
                  ->onDelete('restrict');
            
            $table->foreign('attendance_id', 'fk_transport_costs_attendance')
                  ->references('id')->on('attendances')
                  ->onDelete('restrict');
            
            $table->foreign('approved_by', 'fk_transport_costs_approved_by')
                  ->references('id')->on('users')
                  ->onDelete('set null');
            
            $table->foreign('created_by', 'fk_transport_costs_created_by')
                  ->references('id')->on('users')
                  ->onDelete('restrict');
            
            $table->foreign('updated_by', 'fk_transport_costs_updated_by')
                  ->references('id')->on('users')
                  ->onDelete('set null');
            
            // Unique Constraint: One trip entry per driver per day
            $table->unique(['driver_id', 'trip_date'], 'uk_driver_trip_date');
        });
        
        // Add check constraints using raw SQL (Laravel doesn't support CHECK constraints directly)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE transport_costs ADD CONSTRAINT chk_odometer_valid CHECK (odometer_end >= odometer_start)');
            DB::statement('ALTER TABLE transport_costs ADD CONSTRAINT chk_costs_non_negative CHECK (gasoline_cost >= 0 AND toll_cost >= 0 AND parking_cost >= 0)');
            DB::statement('ALTER TABLE transport_costs ADD CONSTRAINT chk_drop_points_positive CHECK (drop_point_count > 0)');
            DB::statement('ALTER TABLE transport_costs ADD CONSTRAINT chk_delivery_time_valid CHECK (delivery_end_time > delivery_start_time)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_costs');
    }
};
