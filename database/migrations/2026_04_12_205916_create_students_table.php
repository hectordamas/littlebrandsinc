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
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            // Relación con el padre (user)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Datos básicos
            $table->string('name');
            $table->date('birthdate'); // mejor que edad

            // Info importante
            $table->string('level')->nullable(); // principiante, intermedio, etc.
            $table->text('medical_notes')->nullable(); // alergias, condiciones
            $table->text('comment')->nullable();

            // Estado
            $table->boolean('active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
