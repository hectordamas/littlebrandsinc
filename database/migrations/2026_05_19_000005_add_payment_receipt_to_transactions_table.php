<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'payment_receipt_path')) {
                $table->string('payment_receipt_path')->nullable()->after('description');
            }
            if (! Schema::hasColumn('transactions', 'payment_receipt_original_name')) {
                $table->string('payment_receipt_original_name')->nullable()->after('payment_receipt_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['payment_receipt_path', 'payment_receipt_original_name']);
        });
    }
};
