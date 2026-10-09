<?php

use App\Exports\BookingsExport;
use App\Models\Booking;
use App\Models\Villa;
use App\Services\Payment\ManualPaymentService;
use App\Services\Payment\XenditPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;
use Maatwebsite\Excel\Facades\Excel;

// Public guest routes
Volt::route('/', 'guest.home')->name('home');
Volt::route('/villas', 'guest.villas-index')->name('villas.index');
Volt::route('/villas/{slug}', 'guest.villa-detail')->name('villas.show');
Volt::route('/syarat-ketentuan', 'guest.syarat-ketentuan')->name('syarat-ketentuan');

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Volt::route('/dashboard', 'admin.dashboard')->name('dashboard');
    Volt::route('/villas', 'admin.villas.index')->name('villas.index');

    Volt::route('/laporan/{year}/{month}', 'admin.laporan.bulanan')->name('laporan.bulanan');
    Volt::route('/kalender', 'admin.villa-kalender')->name('kalender');

    Route::get('/export/bookings', function (Request $request) {
        $year  = (int) ($request->year  ?? now()->year);
        $month = $request->month ? (int) $request->month : null;

        $filename = $month
            ? 'laporan-boboin-' . \Carbon\Carbon::create($year, $month)->format('Y-m') . '.xlsx'
            : 'laporan-boboin-' . $year . '.xlsx';

        return Excel::download(new BookingsExport($year, $month), $filename);
    })->name('export.bookings');
    Volt::route('/bookings', 'admin.bookings.index')->name('bookings.index');
    Volt::route('/payments', 'admin.payments.index')->name('payments.index');

    // Upload / ganti foto villa
    Route::post('/villas/{id}/image', function (Request $request, int $id) {
        $request->validate([
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'image.required' => 'Pilih file gambar terlebih dahulu.',
            'image.mimes'    => 'Format gambar harus JPG, PNG, atau WebP.',
            'image.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        $villa = Villa::findOrFail($id);

        if ($villa->image) {
            Storage::disk('public')->delete($villa->image);
        }

        $path = $request->file('image')->store('villas', 'public');
        $villa->update(['image' => $path]);

        return redirect()->route('admin.villas.index')
            ->with('image_success', 'Foto villa berhasil diperbarui.');
    })->name('villas.image');
});

// Guest public routes (tanpa login)
Route::name('guest.')->group(function () {
    Volt::route('/cek-pesanan', 'guest.cek-pesanan')->name('cek-pesanan');
    Volt::route('/payment/{bookingCode}', 'guest.payment-upload')->name('payment.upload');

    Route::post('/payment/{bookingCode}/upload', function (Request $request, string $bookingCode) {
        $booking = Booking::with(['villa', 'payment'])
            ->where('booking_code', strtoupper($bookingCode))
            ->where('status', 'approved')
            ->firstOrFail();

        $maxCapacity = $booking->villa->capacity;

        $request->validate([
            'guest_name'  => ['required', 'string', 'max:100'],
            'guest_phone' => ['required', 'string', 'max:20'],
            'guest_email' => ['required', 'email'],
            'guest_city'  => ['required', 'string', 'max:100'],
            'adult_count' => ['required', 'integer', 'min:1', "max:{$maxCapacity}"],
            'child_count' => ['required', 'integer', 'min:0', "max:{$maxCapacity}"],
            'receipt'     => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ], [
            'guest_name.required'  => 'Nama lengkap wajib diisi.',
            'guest_phone.required' => 'Nomor HP wajib diisi.',
            'guest_email.required' => 'Email wajib diisi.',
            'guest_email.email'    => 'Format email tidak valid.',
            'guest_city.required'  => 'Asal kota wajib diisi.',
            'adult_count.required' => 'Jumlah tamu dewasa wajib diisi.',
            'adult_count.min'      => 'Minimal 1 tamu dewasa.',
            'adult_count.max'      => "Jumlah tamu dewasa melebihi kapasitas villa ({$maxCapacity} orang).",
            'child_count.required' => 'Jumlah anak wajib diisi (isi 0 jika tidak ada).',
            'child_count.max'      => "Jumlah anak melebihi kapasitas villa ({$maxCapacity} orang).",
            'receipt.required'     => 'Pilih file bukti transfer terlebih dahulu.',
            'receipt.mimes'        => 'File harus berupa gambar (JPG, PNG, WebP) atau PDF.',
            'receipt.max'          => 'Ukuran file maksimal 5MB.',
        ]);

        $totalGuests = (int) $request->adult_count + (int) $request->child_count;
        if ($totalGuests > $maxCapacity) {
            return back()->withErrors([
                'adult_count' => "Total tamu (dewasa + anak-anak) berjumlah {$totalGuests} orang, melebihi kapasitas maksimal villa ({$maxCapacity} orang).",
            ])->withInput();
        }

        $booking->update([
            'guest_name'  => $request->guest_name,
            'guest_phone' => $request->guest_phone,
            'guest_email' => $request->guest_email,
            'guest_city'  => $request->guest_city,
            'adult_count' => $request->adult_count,
            'child_count' => $request->child_count,
        ]);

        $service = new ManualPaymentService();

        $payment = $booking->payment ?? $service->createPayment($booking, [
            'bank'           => config('payment.manual.bank', 'BCA'),
            'account_number' => config('payment.manual.account_number', '1234567890'),
        ]);

        $service->uploadReceipt($payment, $request->file('receipt'));

        return redirect()->route('guest.payment.upload', $bookingCode)
            ->with('success', 'Bukti transfer berhasil dikirim! Admin akan memverifikasi dalam 1×24 jam.');
    })->name('payment.upload.post');

    Route::post('/payment/{bookingCode}/xendit', function (Request $request, string $bookingCode) {
        $booking = Booking::with(['villa', 'payment'])
            ->where('booking_code', strtoupper($bookingCode))
            ->where('status', 'approved')
            ->firstOrFail();

        $maxCapacity = $booking->villa->capacity;

        $request->validate([
            'guest_name'  => ['required', 'string', 'max:100'],
            'guest_phone' => ['required', 'string', 'max:20'],
            'guest_email' => ['required', 'email'],
            'guest_city'  => ['required', 'string', 'max:100'],
            'adult_count' => ['required', 'integer', 'min:1', "max:{$maxCapacity}"],
            'child_count' => ['required', 'integer', 'min:0', "max:{$maxCapacity}"],
        ], [
            'guest_name.required'  => 'Nama lengkap wajib diisi.',
            'guest_phone.required' => 'Nomor HP wajib diisi.',
            'guest_email.required' => 'Email wajib diisi.',
            'guest_email.email'    => 'Format email tidak valid.',
            'guest_city.required'  => 'Asal kota wajib diisi.',
            'adult_count.min'      => 'Minimal 1 tamu dewasa.',
            'adult_count.max'      => "Jumlah dewasa melebihi kapasitas villa ({$maxCapacity} orang).",
            'child_count.max'      => "Jumlah anak melebihi kapasitas villa ({$maxCapacity} orang).",
        ]);

        $totalGuests = (int) $request->adult_count + (int) $request->child_count;
        if ($totalGuests > $maxCapacity) {
            return back()->withErrors([
                'adult_count' => "Total tamu ({$totalGuests} orang) melebihi kapasitas villa ({$maxCapacity} orang).",
            ])->withInput();
        }

        $booking->update([
            'guest_name'  => $request->guest_name,
            'guest_phone' => $request->guest_phone,
            'guest_email' => $request->guest_email,
            'guest_city'  => $request->guest_city,
            'adult_count' => $request->adult_count,
            'child_count' => $request->child_count,
        ]);

        $service = new XenditPaymentService();
        $payment = $service->createPayment($booking->fresh(['villa']));

        return redirect($payment->xendit_invoice_url);
    })->name('payment.xendit.post');

    Route::get('/invoice/{bookingCode}', function (string $bookingCode) {
        $booking = Booking::with(['villa', 'payment'])
            ->where('booking_code', strtoupper($bookingCode))
            ->where('status', 'paid')
            ->firstOrFail();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', ['booking' => $booking]);

        return $pdf->download('invoice-boboin-' . $bookingCode . '.pdf');
    })->name('invoice.download');
});

// Xendit webhook — CSRF dikecualikan di bootstrap/app.php
Route::get('/xendit/webhook', function () {
    return response('OK', 200);
});

Route::post('/xendit/webhook', function (Request $request) {
    $token = $request->header('x-callback-token');

    if ($token !== config('services.xendit.webhook_token')) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $data       = $request->all();
    $status     = $data['status'] ?? null;
    $externalId = $data['external_id'] ?? null;

    if (!$externalId) {
        return response()->json(['message' => 'Missing external_id'], 400);
    }

    $booking = Booking::with('payment')
        ->where('booking_code', $externalId)
        ->first();

    if (!$booking || !$booking->payment) {
        return response()->json(['message' => 'Booking not found'], 404);
    }

    if (in_array($status, ['PAID', 'SETTLED'])) {
        (new XenditPaymentService())->verifyPayment($booking->payment);
    } elseif ($status === 'EXPIRED') {
        $booking->payment->update(['payment_status' => 'rejected']);
        $booking->update(['status' => 'approved']);
    }

    return response()->json(['message' => 'OK']);
})->name('xendit.webhook');

require __DIR__.'/auth.php';
