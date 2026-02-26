<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Customer who wrote review
            $table->foreignId('service_id')->constrained()->onDelete('cascade'); // Service being reviewed
            $table->foreignId('booking_id')->constrained()->onDelete('cascade'); // Related booking
            $table->integer('rating'); // 1-5 stars
            $table->text('comment')->nullable();
            $table->json('tags')->nullable(); // e.g., ["professional", "on-time", "quality"]
            $table->boolean('is_approved')->default(true); // For moderation
            $table->timestamp('reviewed_at');
            $table->timestamps();

            // Ensure one review per booking
            $table->unique('booking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
