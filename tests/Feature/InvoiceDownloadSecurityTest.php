<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class InvoiceDownloadSecurityTest extends TestCase
{
    public function test_invoice_download_is_throttled_after_ten_attempts_per_ip(): void
    {
        $rateLimitKey = 'invoice-download:127.0.0.1';
        RateLimiter::clear($rateLimitKey);

        for ($attempt = 0; $attempt < 10; $attempt++) {
            RateLimiter::hit($rateLimitKey, 60);
        }

        $this->get('/invoice/BVL-ABC123')
            ->assertStatus(429)
            ->assertHeader('Retry-After');
    }
}