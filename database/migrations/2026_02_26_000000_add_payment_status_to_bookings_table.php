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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_status')->default('pending')->after('status'); // pending, paid, refunded, failed
            $table->string('payment_method')->nullable()->after('payment_status');
            $table->timestamp('paid_at')->nullable()->after('payment_method');
            $table->text('payment_notes')->nullable()->after('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_method', 'paid_at', 'payment_notes']);
        });
    }
};
