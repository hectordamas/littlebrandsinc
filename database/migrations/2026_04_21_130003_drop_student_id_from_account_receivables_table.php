<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('account_receivables') || !Schema::hasColumn('account_receivables', 'student_id')) {
            return;
        }

        Schema::table('account_receivables', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('account_receivables') || Schema::hasColumn('account_receivables', 'student_id')) {
            return;
        }

        Schema::table('account_receivables', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable()->after('enrollment_id')->constrained()->nullOnDelete();
        });
    }
};
