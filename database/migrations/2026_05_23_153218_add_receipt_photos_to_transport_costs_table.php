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
            $table->string('gasoline_receipt_path', 1000)->nullable()->after('finance_word_path');
            $table->string('toll_receipt_path', 1000)->nullable()->after('gasoline_receipt_path');
            $table->string('parking_receipt_path', 1000)->nullable()->after('toll_receipt_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transport_costs', function (Blueprint $table) {
            $table->dropColumn([
                'gasoline_receipt_path',
                'toll_receipt_path',
                'parking_receipt_path'
            ]);
        });
    }
};
