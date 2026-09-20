<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('enrollment_course') && !Schema::hasColumn('enrollment_course', 'status')) {
            Schema::table('enrollment_course', function (Blueprint $table) {
                $table->string('status', 20)->default('active')->after('custom_amount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('enrollment_course') && Schema::hasColumn('enrollment_course', 'status')) {
            Schema::table('enrollment_course', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
