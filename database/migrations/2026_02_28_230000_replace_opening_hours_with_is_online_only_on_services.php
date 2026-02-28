<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('opening_hours');
            $table->boolean('is_online_only')->default(false)->after('is_available');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('opening_hours')->nullable()->after('address');
            $table->dropColumn('is_online_only');
        });
    }
};
