<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->decimal('min_age', 4, 1)->nullable()->change();
            $table->decimal('max_age', 4, 1)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->integer('min_age')->nullable()->change();
            $table->integer('max_age')->nullable()->change();
        });
    }
};
