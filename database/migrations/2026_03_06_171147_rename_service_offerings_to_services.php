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
        Schema::rename('service_offerings', 'services');

        Schema::table('bookings', function (Blueprint $blueprint) {
            $blueprint->renameColumn('service_offering_id', 'service_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $blueprint) {
            $blueprint->renameColumn('service_id', 'service_offering_id');
        });

        Schema::rename('services', 'service_offerings');
    }
};
