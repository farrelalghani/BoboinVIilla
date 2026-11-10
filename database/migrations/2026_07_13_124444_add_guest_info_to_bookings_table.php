<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Lepas FK guest_id supaya bisa dijadikan nullable
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['guest_id']);
        });

        DB::statement('ALTER TABLE bookings MODIFY COLUMN guest_id BIGINT UNSIGNED NULL');

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('guest_id')->references('id')->on('users')->nullOnDelete();
            $table->string('booking_code', 20)->unique()->nullable()->after('id');
            $table->string('guest_name')->nullable()->after('booking_code');
            $table->string('guest_email')->nullable()->after('guest_name');
            $table->string('guest_phone', 20)->nullable()->after('guest_email');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['guest_id']);
            $table->dropColumn(['booking_code', 'guest_name', 'guest_email', 'guest_phone']);
        });

        DB::statement('ALTER TABLE bookings MODIFY COLUMN guest_id BIGINT UNSIGNED NOT NULL');

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('guest_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
