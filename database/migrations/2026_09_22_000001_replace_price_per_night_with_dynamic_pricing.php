<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('villas', function (Blueprint $table) {
            $table->decimal('weekday_price', 12, 2)->after('price_per_night')->default(0);
            $table->decimal('weekend_price', 12, 2)->after('weekday_price')->default(0);
        });

        DB::table('villas')->update([
            'weekday_price' => DB::raw('price_per_night'),
            'weekend_price' => DB::raw('price_per_night'),
        ]);

        Schema::table('villas', function (Blueprint $table) {
            $table->dropColumn('price_per_night');
        });
    }

    public function down(): void
    {
        Schema::table('villas', function (Blueprint $table) {
            $table->decimal('price_per_night', 12, 2)->after('description')->default(0);
        });

        DB::table('villas')->update([
            'price_per_night' => DB::raw('weekday_price'),
        ]);

        Schema::table('villas', function (Blueprint $table) {
            $table->dropColumn(['weekday_price', 'weekend_price']);
        });
    }
};
