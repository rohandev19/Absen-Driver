<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_reports', function (Blueprint $table) {
            // Path PDF.
            $table->string('admin_internal_pdf_path')->nullable()->after('customer_signed_document_path');
            $table->string('finance_pdf_path')->nullable()->after('finance_word_path');

            // Field finance/internal. Nullable agar tidak merusak data lama.
            $table->string('workshop_name')->nullable()->after('finance_pdf_path');
            $table->string('invoice_number')->nullable()->after('workshop_name');
            $table->decimal('service_cost', 15, 2)->nullable()->after('invoice_number');
            $table->decimal('sparepart_cost', 15, 2)->nullable()->after('service_cost');
            $table->decimal('other_cost', 15, 2)->nullable()->after('sparepart_cost');
            $table->decimal('total_cost', 15, 2)->nullable()->after('other_cost');
            $table->text('finance_notes')->nullable()->after('total_cost');
        });
    }

    public function down(): void
    {
        Schema::table('service_reports', function (Blueprint $table) {
            $table->dropColumn([
                'admin_internal_pdf_path',
                'finance_pdf_path',
                'workshop_name',
                'invoice_number',
                'service_cost',
                'sparepart_cost',
                'other_cost',
                'total_cost',
                'finance_notes',
            ]);
        });
    }
};
