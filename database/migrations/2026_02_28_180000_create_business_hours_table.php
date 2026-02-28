<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0 = Sunday, 6 = Saturday
            $table->string('time_from', 5); // e.g. "09:00"
            $table->string('time_to', 5);   // e.g. "17:00"
            $table->timestamps();

            $table->unique(['service_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_hours');
    }
};
