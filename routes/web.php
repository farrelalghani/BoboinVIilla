<?php

use App\Exports\BookingsExport;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Villa;
use App\Services\Payment\XenditPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;
use Maatwebsite\Excel\Facades\Excel;

// Language switch route
Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['id', 'en'], true)) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return redirect()->back(fallback: route('home'));
})->name('set-locale');

// Public guest routes
Volt::route('/', 'guest.home')->name('home');
Volt::route('/villas', 'guest.villas-index')->name('villas.index');
Volt::route('/villas/{slug}', 'guest.villa-detail')->name('villas.show');
Volt::route('/syarat-ketentuan', 'guest.syarat-ketentuan')->name('syarat-ketentuan');

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Volt::route('/dashboard', 'admin.dashboard')->name('dashboard');
    Volt::route('/villas', 'admin.villas.index')->name('villas.index');
    Volt::route('/pengaturan', 'admin.pengaturan.index')->name('pengaturan.index');

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

    Route::get('/payments/{payment}/receipt', function (Payment $payment) {
        abort_unless(
            $payment->receipt_image && Storage::disk('local')->exists($payment->receipt_image),
            404
        );

        return Storage::disk('local')->download($payment->receipt_image);
    })->name('payments.receipt');

    // Upload foto villa (mendukung banyak foto sekaligus)
    Route::post('/villas/{id}/images', function (Request $request, int $id) {
        $files = $request->file('images');
        if (! $files && $request->hasFile('image')) {
            $files = [$request->file('image')];
        }

        $request->validate([
            'images'   => ['nullable', 'array'],
            'images.*' => ['file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:5120'],
            'image'    => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:5120'],
        ], [
            'images.*.mimetypes' => 'Format foto harus JPG, PNG, atau WebP.',
            'images.*.max'       => 'Ukuran per foto maksimal 5MB.',
            'image.mimetypes'    => 'Format foto harus JPG, PNG, atau WebP.',
            'image.max'          => 'Ukuran foto maksimal 5MB.',
        ]);

        if (empty($files)) {
            return back()->withErrors(['images' => 'Pilih minimal satu file foto untuk diunggah.']);
        }

        $villa = Villa::findOrFail($id);
        $currentImages = is_array($villa->images) ? $villa->images : [];

        $newPaths = [];
        foreach ($files as $file) {
            $newPaths[] = $file->store('villas', 'public');
        }

        $merged = array_values(array_unique(array_merge($currentImages, $newPaths)));
        $primary = $villa->image ?: ($merged[0] ?? null);

        $villa->update([
            'image'  => $primary,
            'images' => $merged,
        ]);

        return redirect()->route('admin.villas.index')
            ->with('image_success', count($newPaths).' foto berhasil ditambahkan ke galeri.');
    })->name('villas.images');

    // Route alias untuk kompatibilitas form lama
    Route::post('/villas/{id}/image', function (Request $request, int $id) {
        $villa = Villa::findOrFail($id);

        $request->validate([
            'image' => ['required', 'file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:5120'],
        ], [
            'image.required'  => 'Pilih file gambar terlebih dahulu.',
            'image.mimetypes' => 'Format gambar harus JPG, PNG, atau WebP.',
            'image.max'       => 'Ukuran file maksimal 5MB.',
        ]);

        $path = $request->file('image')->store('villas', 'public');
        $current = is_array($villa->images) ? $villa->images : [];
        if ($villa->image && ! in_array($villa->image, $current, true)) {
            $current[] = $villa->image;
        }
        $current[] = $path;

        $villa->update([
            'image'  => $path,
            'images' => array_values(array_unique($current)),
        ]);

        return redirect()->route('admin.villas.index')
            ->with('image_success', 'Foto villa berhasil diperbarui.');
    })->name('villas.image');

    // Jadikan foto sebagai cover / foto utama
    Route::post('/villas/{id}/images/primary', function (Request $request, int $id) {
        $request->validate(['path' => ['required', 'string']]);
        $villa = Villa::findOrFail($id);
        $path = $request->input('path');

        if (in_array($path, $villa->all_images, true)) {
            $villa->update(['image' => $path]);
        }

        return redirect()->route('admin.villas.index')
            ->with('image_success', 'Foto utama (cover) berhasil diubah.');
    })->name('villas.images.primary');

    // Hapus satu foto dari galeri villa
    Route::delete('/villas/{id}/images', function (Request $request, int $id) {
        $request->validate(['path' => ['required', 'string']]);
        $villa = Villa::findOrFail($id);
        $path = $request->input('path');

        $all = $villa->all_images;
        if (in_array($path, $all, true)) {
            Storage::disk('public')->delete($path);
            $remaining = array_values(array_filter($all, fn ($p) => $p !== $path));
            $newPrimary = ($villa->image === $path) ? ($remaining[0] ?? null) : $villa->image;

            $villa->update([
                'image'  => $newPrimary,
                'images' => $remaining,
            ]);
        }

        return redirect()->route('admin.villas.index')
            ->with('image_success', 'Foto berhasil dihapus.');
    })->name('villas.images.delete');

    // Update banner cover beranda tamu
    Route::post('/pengaturan/banner', function (Request $request) {
        $request->validate([
            'hero_image'    => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:5120'],
            'hero_title'    => ['nullable', 'string', 'max:150'],
            'hero_subtitle' => ['nullable', 'string', 'max:255'],
        ], [
            'hero_image.mimetypes' => 'Format foto cover harus JPG, PNG, atau WebP.',
            'hero_image.max'       => 'Ukuran foto cover maksimal 5MB.',
        ]);

        if ($request->hasFile('hero_image')) {
            $old = \App\Models\SiteSetting::get('hero_image');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
            $path = $request->file('hero_image')->store('banners', 'public');
            \App\Models\SiteSetting::set('hero_image', $path);
        }

        if ($request->has('hero_title')) {
            \App\Models\SiteSetting::set('hero_title', $request->input('hero_title'));
        }

        if ($request->has('hero_subtitle')) {
            \App\Models\SiteSetting::set('hero_subtitle', $request->input('hero_subtitle'));
        }

        return redirect()->route('admin.pengaturan.index')
            ->with('success', 'Banner cover beranda berhasil diperbarui!');
    })->name('pengaturan.banner');

    // Reset banner cover beranda ke bawaan
    Route::post('/pengaturan/banner/reset', function () {
        $old = \App\Models\SiteSetting::get('hero_image');
        if ($old && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }
        \App\Models\SiteSetting::set('hero_image', null);
        \App\Models\SiteSetting::set('hero_title', null);
        \App\Models\SiteSetting::set('hero_subtitle', null);

        return redirect()->route('admin.pengaturan.index')
            ->with('success', 'Foto cover beranda berhasil dikembalikan ke tampilan bawaan.');
    })->name('pengaturan.banner.reset');
});

// Guest public routes (tanpa login)
Route::name('guest.')->group(function () {
    Volt::route('/cek-pesanan', 'guest.cek-pesanan')->name('cek-pesanan');
    Volt::route('/payment/{bookingCode}', 'guest.payment-upload')->name('payment.upload');

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
    })->middleware('throttle:5,1')->name('payment.xendit.post');

    Route::get('/invoice/{bookingCode}', function (string $bookingCode) {
        $rateLimitKey = 'invoice-download:'.request()->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
            return response('Too many invoice download attempts.', 429, [
                'Retry-After' => RateLimiter::availableIn($rateLimitKey),
            ]);
        }

        RateLimiter::hit($rateLimitKey, 60);

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
    $status = $request->input('status');
    $externalId = $request->input('external_id');
    $invoiceId = $request->input('id');

    $expectedToken = (string) config('services.xendit.webhook_token');
    if ($expectedToken === '' || ! $token || ! hash_equals($expectedToken, (string) $token)) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    if (!$externalId) {
        return response()->json(['message' => 'Missing external_id'], 400);
    }

    $allowedStatuses = ['PAID', 'SETTLED', 'EXPIRED'];
    if (!in_array($status, $allowedStatuses, true)) {
        return response()->json(['message' => 'Unsupported Xendit status'], 400);
    }

    if (!$invoiceId) {
        return response()->json(['message' => 'Missing invoice id'], 400);
    }

    $booking = Booking::with('payment')
        ->where('booking_code', $externalId)
        ->first();

    if (!$booking || !$booking->payment) {
        return response()->json(['message' => 'Booking not found'], 404);
    }

    if ($booking->payment->xendit_invoice_id !== $invoiceId) {
        return response()->json(['message' => 'Invoice mismatch'], 400);
    }

    try {
        $service = new XenditPaymentService();
        $processed = in_array($status, ['PAID', 'SETTLED'], true)
            ? $service->verifyPayment($booking->payment)
            : $service->expirePayment($booking->payment);
    } catch (\Xendit\XenditSdkException $exception) {
        report($exception);

        return response()->json(['message' => 'Unable to verify invoice with Xendit'], 503);
    }

    if (! $processed) {
        return response()->json(['message' => 'Invoice status or booking state mismatch'], 409);
    }

    return response()->json(['message' => 'OK']);
})->name('xendit.webhook');

require __DIR__.'/auth.php';
