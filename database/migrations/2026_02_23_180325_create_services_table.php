<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->tinyInteger('price_range')->default(2); // 1=$, 2=$$, 3=$$$, 4=$$$$
            $table->string('badge')->nullable(); // e.g. "EMERGENCY SERVICE", "CERTIFIED PRO", "ECO-FRIENDLY"
            $table->string('badge_color')->nullable(); // e.g. "blue", "gray", "green"
            $table->string('image')->nullable();
            $table->boolean('is_available')->default(true);
            $table->string('available_at')->nullable(); // e.g. "Available today at 2:00 PM"
            $table->decimal('rating', 3, 1)->default(0);
            $table->integer('reviews_count')->default(0);
            $table->string('city')->default('New York');
            $table->string('state')->default('NY');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
