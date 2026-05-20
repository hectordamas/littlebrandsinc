<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'program_id')) {
                $table->foreignId('program_id')->nullable()->after('id')->constrained('programs')->nullOnDelete();
                $table->index('program_id', 'courses_program_idx');
            }
        });

        $defaultProgramId = DB::table('programs')->where('slug', 'little-strikers')->value('id');
        if ($defaultProgramId) {
            DB::table('courses')->whereNull('program_id')->update(['program_id' => $defaultProgramId]);
        }
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'program_id')) {
                $table->dropForeign(['program_id']);
                $table->dropIndex('courses_program_idx');
                $table->dropColumn('program_id');
            }
        });
    }
};
