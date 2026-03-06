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
        Schema::rename('services', 'shops');
        Schema::rename('service_images', 'shop_images');

        $tables = ['bookmarks', 'business_hours', 'reviews', 'bookings', 'shop_images', 'service_offerings'];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->renameColumn('service_id', 'shop_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['bookmarks', 'business_hours', 'reviews', 'bookings', 'shop_images', 'service_offerings'];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->renameColumn('shop_id', 'service_id');
            });
        }

        Schema::rename('shop_images', 'service_images');
        Schema::rename('shops', 'services');
    }
};
