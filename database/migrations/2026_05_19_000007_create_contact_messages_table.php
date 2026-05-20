<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('representative_name', 160);
            $table->string('child_name', 160);
            $table->unsignedTinyInteger('child_age')->nullable();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('phone', 40);
            $table->string('email', 180);
            $table->text('comment');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('read_at', 'contact_messages_read_at_idx');
            $table->index('created_at', 'contact_messages_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
