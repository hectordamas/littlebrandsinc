<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('account_receivable_payments')) {
            Schema::drop('account_receivable_payments');
        }

        if (Schema::hasTable('account_payable_payments')) {
            Schema::drop('account_payable_payments');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('account_receivable_payments')) {
            Schema::create('account_receivable_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('account_receivable_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
                $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
                $table->decimal('amount', 12, 2);
                $table->string('currency', 3)->default('USD');
                $table->date('payment_date');
                $table->string('reference')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('account_payable_payments')) {
            Schema::create('account_payable_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('account_payable_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
                $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
                $table->decimal('amount', 12, 2);
                $table->string('currency', 3)->default('USD');
                $table->date('payment_date');
                $table->string('reference')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }
};
