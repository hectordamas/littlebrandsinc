<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('account_receivable_id')->nullable()->after('account_id')->constrained('account_receivables')->nullOnDelete();
            $table->foreignId('account_payable_id')->nullable()->after('account_receivable_id')->constrained('account_payables')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['account_receivable_id']);
            $table->dropForeign(['account_payable_id']);
            $table->dropColumn(['account_receivable_id', 'account_payable_id']);
        });
    }
};
