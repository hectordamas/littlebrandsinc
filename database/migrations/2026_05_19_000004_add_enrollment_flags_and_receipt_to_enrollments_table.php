<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('enrollments', 'is_free_trial')) {
                $table->boolean('is_free_trial')->default(false)->after('payment_status');
            }
            if (! Schema::hasColumn('enrollments', 'terms_accepted')) {
                $table->boolean('terms_accepted')->default(false)->after('is_free_trial');
            }
            if (! Schema::hasColumn('enrollments', 'image_consent_accepted')) {
                $table->boolean('image_consent_accepted')->default(false)->after('terms_accepted');
            }
            if (! Schema::hasColumn('enrollments', 'payment_receipt_path')) {
                $table->string('payment_receipt_path')->nullable()->after('image_consent_accepted');
            }
            if (! Schema::hasColumn('enrollments', 'payment_receipt_original_name')) {
                $table->string('payment_receipt_original_name')->nullable()->after('payment_receipt_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn([
                'is_free_trial',
                'terms_accepted',
                'image_consent_accepted',
                'payment_receipt_path',
                'payment_receipt_original_name',
            ]);
        });
    }
};
