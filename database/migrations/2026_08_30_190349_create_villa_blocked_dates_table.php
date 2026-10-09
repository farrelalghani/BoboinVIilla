<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villa_blocked_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('villa_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->unique(['villa_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villa_blocked_dates');
    }
};
