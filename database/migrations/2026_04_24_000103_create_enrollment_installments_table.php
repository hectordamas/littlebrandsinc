<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('enrollment_installments')) {
            return;
        }

        Schema::create('enrollment_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_receivable_id')->nullable()->constrained('account_receivables')->nullOnDelete();
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month');
            $table->date('due_date');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('status', 24)->default('pending');
            $table->boolean('is_first_month')->default(false);
            $table->string('stripe_invoice_id')->nullable()->index();
            $table->string('stripe_payment_intent_id')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('notified_d3_at')->nullable();
            $table->timestamp('notified_d1_at')->nullable();
            $table->timestamp('notified_d0_at')->nullable();
            $table->timestamp('notified_d3_plus_at')->nullable();
            $table->unsignedSmallInteger('retry_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['enrollment_id', 'period_year', 'period_month'], 'enr_inst_period_unique');
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('enrollment_installments')) {
            Schema::dropIfExists('enrollment_installments');
        }
    }
};
