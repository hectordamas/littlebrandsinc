<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('account_receivable_id')->constrained('account_receivables')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('reference', 255)->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('receipt_original_name')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejected_reason')->nullable();
            $table->timestamps();

            $table->index(['status', 'user_id'], 'parent_payments_status_user_idx');
            $table->index(['account_receivable_id', 'status'], 'parent_payments_receivable_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_payments');
    }
};
