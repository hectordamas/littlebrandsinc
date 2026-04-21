<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('account_id');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('type', ['income', 'expense']);
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->string('payment_method', 32)->nullable();
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE transactions ADD CONSTRAINT transactions_enrollment_requires_student_course CHECK ((enrollment_id IS NULL) OR (student_id IS NOT NULL AND course_id IS NOT NULL))');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE transactions DROP CONSTRAINT IF EXISTS transactions_enrollment_requires_student_course');
        Schema::dropIfExists('transactions');
    }
};
