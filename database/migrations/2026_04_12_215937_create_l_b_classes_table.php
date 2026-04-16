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
        Schema::create('l_b_classes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
           
            $table->decimal('price', 8, 2)->nullable();

            $table->string('day'); // Monday
            $table->time('start_time');
            $table->time('end_time');

            $table->string('location')->nullable();
            $table->foreignId('coach_id')->nullable()->constrained('users')->nullOnDelete();

            $table->integer('capacity')->default(20);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('l_b_classes');
    }
};
