<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->boolean('is_temporary')->default(false)->after('status');
            $table->string('verification_status', 30)->default('verified')->after('is_temporary');
            $table->foreignId('verified_by')->nullable()->after('verification_status')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->string('source', 30)->default('admin')->after('verified_at');
            $table->text('notes')->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'is_temporary',
                'verification_status',
                'verified_by',
                'verified_at',
                'source',
                'notes',
            ]);
        });
    }
};
