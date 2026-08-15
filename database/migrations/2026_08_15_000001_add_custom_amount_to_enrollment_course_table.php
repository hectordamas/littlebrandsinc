<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('enrollment_course') && !Schema::hasColumn('enrollment_course', 'custom_amount')) {
            Schema::table('enrollment_course', function (Blueprint $table) {
                $table->decimal('custom_amount', 10, 2)->nullable()->after('course_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('enrollment_course') && Schema::hasColumn('enrollment_course', 'custom_amount')) {
            Schema::table('enrollment_course', function (Blueprint $table) {
                $table->dropColumn('custom_amount');
            });
        }
    }
};
