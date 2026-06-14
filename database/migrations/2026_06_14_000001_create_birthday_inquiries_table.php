<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('birthday_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('representative_name', 160);
            $table->string('phone', 40);
            $table->string('email', 180);
            $table->unsignedTinyInteger('age_to_celebrate');
            $table->date('event_date');
            $table->string('start_time', 20);
            $table->string('location_type', 40); // e.g. 'sede_san_luis', 'sede_los_campitos', 'sede_los_chorros', 'other'
            $table->string('event_location', 255)->nullable(); // Lugar del evento si es otra ubicacion o adicional
            $table->unsignedInteger('estimated_children');
            $table->string('guest_age_range', 100);
            $table->string('program_interest', 40); // 'strikers' or 'paddlers'
            $table->json('additional_services')->nullable(); // checked additional services
            $table->text('comments')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('read_at', 'birthday_inquiries_read_at_idx');
            $table->index('created_at', 'birthday_inquiries_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('birthday_inquiries');
    }
};
