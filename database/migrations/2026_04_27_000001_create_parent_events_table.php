<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_events', function (Blueprint $table) {
            $table->id();
            $table->string('type', 40); // season_start | tournament | event
            $table->string('title', 180);
            $table->text('message');
            $table->date('event_date')->nullable();
            $table->timestamp('send_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_events');
    }
};
