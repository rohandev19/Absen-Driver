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
        Schema::table('transport_costs', function (Blueprint $table) {
            $table->boolean('submitted_to_finance')->default(false)->after('rejection_reason');
            $table->dateTime('submitted_to_finance_at')->nullable()->after('submitted_to_finance');
            $table->unsignedBigInteger('submitted_to_finance_by')->nullable()->after('submitted_to_finance_at');
            $table->string('finance_word_path', 1000)->nullable()->after('submitted_to_finance_by');

            $table->foreign('submitted_to_finance_by', 'fk_transport_costs_submitted_to_finance_by')
                  ->references('id')->on('users')
                  ->onDelete('set null');

            $table->index('submitted_to_finance', 'idx_submitted_to_finance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transport_costs', function (Blueprint $table) {
            $table->dropForeign('fk_transport_costs_submitted_to_finance_by');
            $table->dropIndex('idx_submitted_to_finance');
            $table->dropColumn([
                'submitted_to_finance',
                'submitted_to_finance_at',
                'submitted_to_finance_by',
                'finance_word_path'
            ]);
        });
    }
};
