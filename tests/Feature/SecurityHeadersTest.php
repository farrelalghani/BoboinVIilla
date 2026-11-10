<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_security_headers_are_set_in_production(): void
    {
        $this->app['env'] = 'production';
        config()->set('services.xendit.webhook_token', 'valid-token');

        $response = $this->postJson('/xendit/webhook', [
            'status' => 'PENDING',
            'external_id' => 'BVL-ABC123',
        ], [
            'x-callback-token' => 'wrong-token',
        ]);

        $response->assertStatus(401)
            ->assertHeader('strict-transport-security', 'max-age=31536000; includeSubDomains')
            ->assertHeader('x-content-type-options', 'nosniff')
            ->assertHeader('x-frame-options', 'SAMEORIGIN')
            ->assertHeader('referrer-policy', 'strict-origin-when-cross-origin');
    }

    public function test_sensitive_pages_do_not_cache_in_production(): void
    {
        $this->app['env'] = 'production';

        $request = Request::create('/admin/dashboard', 'GET');
        $response = new Response('ok');

        $middleware = new \App\Http\Middleware\SecurityHeaders();
        $result = $middleware->handle($request, fn () => $response);

        $cacheControl = $result->headers->get('Cache-Control');

        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);
        $this->assertSame('no-cache', $result->headers->get('Pragma'));
        $this->assertSame('0', $result->headers->get('Expires'));
    }

    public function test_public_booking_status_pages_do_not_cache_in_production(): void
    {
        $this->app['env'] = 'production';

        $request = Request::create('/cek-pesanan?kode=BVL-ABC123', 'GET');
        $response = new Response('booking status');
        $middleware = new \App\Http\Middleware\SecurityHeaders();

        $result = $middleware->handle($request, fn () => $response);

        $this->assertStringContainsString('no-store', $result->headers->get('Cache-Control'));
        $this->assertSame('no-cache', $result->headers->get('Pragma'));
    }
}
