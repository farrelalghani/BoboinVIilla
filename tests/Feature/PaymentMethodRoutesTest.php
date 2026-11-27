<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PaymentMethodRoutesTest extends TestCase
{
    public function test_manual_receipt_upload_is_enabled_with_rate_limit_and_xendit_checkout_is_disabled(): void
    {
        $this->assertTrue(Route::has('guest.payment.upload.post'));
        $this->assertFalse(Route::has('guest.payment.xendit.post'));

        $uploadRoute = Route::getRoutes()->getByName('guest.payment.upload.post');
        $this->assertContains('throttle:5,1', $uploadRoute->gatherMiddleware());

        $bankSettingsRoute = Route::getRoutes()->getByName('admin.pengaturan.rekening');
        $this->assertNotNull($bankSettingsRoute);
        $this->assertContains('auth', $bankSettingsRoute->gatherMiddleware());
        $this->assertContains('role:admin', $bankSettingsRoute->gatherMiddleware());
    }

    public function test_xendit_webhook_is_blocked_when_manual_driver_is_active(): void
    {
        config()->set('payment.driver', 'manual_transfer');
        config()->set('services.xendit.webhook_token', 'valid-token');

        $response = $this->postJson('/xendit/webhook', [
            'status'      => 'PAID',
            'external_id' => 'BVL-ABC123',
            'id'          => 'inv_12345',
        ], [
            'x-callback-token' => 'valid-token',
        ]);

        $response->assertStatus(503)
            ->assertJson(['message' => 'Xendit gateway is disabled']);
    }

    public function test_guests_cannot_update_admin_bank_settings(): void
    {
        $response = $this->post('/admin/pengaturan/rekening', [
            'payment_manual_bank'           => 'BCA',
            'payment_manual_account_number' => '99999999',
            'payment_manual_account_name'   => 'Attacker',
        ]);

        $response->assertRedirect('/login');
    }
}
