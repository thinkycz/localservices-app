<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('provider_onboarding_pending')->default(false)->after('is_vendor');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->string('customer_name')->nullable()->after('provider_id');
            $table->string('customer_email')->nullable()->after('customer_name');
            $table->string('customer_phone', 30)->nullable()->after('customer_email');
            $table->string('guest_token_hash', 64)->nullable()->unique()->after('customer_phone');
            $table->decimal('price_amount', 10, 2)->nullable()->after('guest_token_hash');
            $table->string('currency', 3)->default('CZK')->after('price_amount');
            $table->string('timezone', 64)->default('Europe/Prague')->after('currency');
            $table->text('cancellation_reason')->nullable()->after('customer_notes');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(
                ['shop_id', 'booking_date', 'status', 'start_time', 'end_time'],
                'bookings_availability_lookup_index'
            );
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->string('timezone', 64)->default('Europe/Prague')->after('currency');
            $table->string('contact_email')->nullable()->after('timezone');
            $table->string('contact_phone', 30)->nullable()->after('contact_email');
            $table->string('city')->nullable()->default(null)->change();
            $table->string('state')->nullable()->default(null)->change();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->boolean('is_available')->default(true)->after('price');
        });

        DB::table('bookings')->orderBy('id')->each(function (object $booking): void {
            $customer = $booking->user_id
                ? DB::table('users')->where('id', $booking->user_id)->first()
                : null;
            $service = DB::table('services')->where('id', $booking->service_id)->first();
            $shop = DB::table('shops')->where('id', $booking->shop_id)->first();

            DB::table('bookings')->where('id', $booking->id)->update([
                'customer_name' => $customer?->name,
                'customer_email' => $customer?->email,
                'customer_phone' => $customer?->phone,
                'price_amount' => $service?->price,
                'currency' => $shop?->currency ?? 'CZK',
                'timezone' => $shop?->timezone ?? 'Europe/Prague',
            ]);
        });
    }

    public function down(): void
    {
        DB::table('bookings')->whereNull('user_id')->delete();

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('is_available');
        });

        DB::table('shops')->whereNull('city')->update(['city' => '']);
        DB::table('shops')->whereNull('state')->update(['state' => '']);

        Schema::table('shops', function (Blueprint $table) {
            $table->string('city')->nullable(false)->default('')->change();
            $table->string('state')->nullable(false)->default('')->change();
            $table->dropColumn(['timezone', 'contact_email', 'contact_phone']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_availability_lookup_index');
            $table->dropForeign(['user_id']);
            $table->dropUnique(['guest_token_hash']);
            $table->dropColumn([
                'customer_name',
                'customer_email',
                'customer_phone',
                'guest_token_hash',
                'price_amount',
                'currency',
                'timezone',
                'cancellation_reason',
            ]);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('provider_onboarding_pending');
        });
    }
};
