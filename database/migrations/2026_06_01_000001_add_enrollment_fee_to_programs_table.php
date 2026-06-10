<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            if (! Schema::hasColumn('programs', 'enrollment_fee')) {
                $table->decimal('enrollment_fee', 8, 2)->default(50.00)->after('description');
            }
        });

        DB::table('programs')->update(['enrollment_fee' => 50.00]);
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('enrollment_fee');
        });
    }
};
