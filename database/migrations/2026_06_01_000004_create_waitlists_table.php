<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waitlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('waiting');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'course_id'], 'waitlists_student_course_unique');
            $table->index(['status', 'course_id'], 'waitlists_status_course_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waitlists');
    }
};
