<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_offerings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2);
            $table->integer('duration_minutes')->default(60);
            $table->boolean('is_popular')->default(false);
            $table->string('category_tag')->nullable(); // e.g. "Haircuts", "Shaves", "Drain", "Electrical"
            $table->string('staff_level')->nullable();  // e.g. "Senior Barber", "Master Electrician"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_offerings');
    }
};
