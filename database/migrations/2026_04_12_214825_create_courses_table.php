<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('name'); // Sub-10
            $table->string('level')->nullable();
            $table->integer('min_age')->nullable();
            $table->integer('max_age')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->boolean('active')->default(true);

            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('active')->default(true);

            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
