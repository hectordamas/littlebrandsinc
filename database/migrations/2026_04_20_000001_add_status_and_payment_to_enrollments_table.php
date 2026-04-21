<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('enrollments', 'status')) {
                $table->string('status')->default('pending')->after('parent_id');
            }
            if (! Schema::hasColumn('enrollments', 'payment_method')) {
                $table->string('payment_method', 32)->nullable()->after('status');
            }
            if (! Schema::hasColumn('enrollments', 'payment_status')) {
                $table->string('payment_status', 32)->nullable()->after('payment_method');
            }
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['status', 'payment_method', 'payment_status']);
        });
    }
};
