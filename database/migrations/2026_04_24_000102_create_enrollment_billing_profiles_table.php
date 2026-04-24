<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollment_billing_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('billing_mode', 24)->default('manual');
            $table->boolean('auto_pay_enabled')->default(false);
            $table->string('stripe_customer_id')->nullable()->index();
            $table->string('stripe_subscription_id')->nullable()->index();
            $table->string('stripe_default_payment_method_id')->nullable();
            $table->unsignedTinyInteger('billing_anchor_day')->nullable();
            $table->date('next_billing_date')->nullable();
            $table->string('status', 24)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_billing_profiles');
    }
};
