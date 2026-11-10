<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Volt;
use Tests\TestCase;

class BookingLookupSecurityTest extends TestCase
{
    public function test_booking_lookup_is_throttled_after_ten_attempts_per_ip(): void
    {
        $rateLimitKey = 'booking-lookup:127.0.0.1';
        RateLimiter::clear($rateLimitKey);
        RateLimiter::hit($rateLimitKey, 60);

        for ($attempt = 1; $attempt < 10; $attempt++) {
            RateLimiter::hit($rateLimitKey, 60);
        }

        Volt::test('guest.cek-pesanan')
            ->set('kode', 'BVL-ABC123')
            ->call('cariPesanan')
            ->assertHasErrors(['kode']);
    }
}