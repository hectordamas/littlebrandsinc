<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_coach', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('coach_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['course_id', 'coach_id'], 'course_coach_unique_idx');
            $table->index(['coach_id', 'course_id'], 'course_coach_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_coach');
    }
};
