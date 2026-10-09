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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('payment_type')->nullable();         // 'manual_transfer' | 'midtrans' | dll
            $table->string('bank')->nullable();                 // BCA, BRI, Mandiri, dst
            $table->string('account_number')->nullable();       // nomor rekening tujuan
            $table->string('receipt_image')->nullable();        // path file bukti transfer
            $table->decimal('gross_amount', 12, 2);
            $table->enum('payment_status', [
                'pending',   // baru dibuat, belum ada aksi
                'uploaded',  // tamu sudah upload struk, menunggu admin
                'verified',  // admin konfirmasi mutasi sah
                'rejected',  // struk tidak valid / tidak sesuai
            ])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
