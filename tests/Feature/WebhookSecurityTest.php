<?php

namespace Tests\Feature;

use Tests\TestCase;

class WebhookSecurityTest extends TestCase
{
    public function test_xendit_webhook_rejects_invalid_callback_token(): void
    {
        config()->set('services.xendit.webhook_token', 'valid-token');

        $response = $this->postJson('/xendit/webhook', [
            'status' => 'PAID',
            'external_id' => 'BVL-ABC123',
        ], [
            'x-callback-token' => 'wrong-token',
        ]);

        $response->assertStatus(401);
    }

    public function test_xendit_webhook_rejects_unsupported_status_even_with_valid_token(): void
    {
        config()->set('services.xendit.webhook_token', 'valid-token');

        $response = $this->postJson('/xendit/webhook', [
            'status' => 'PENDING',
            'external_id' => 'BVL-ABC123',
        ], [
            'x-callback-token' => 'valid-token',
        ]);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Unsupported Xendit status']);
    }

    public function test_xendit_webhook_requires_invoice_id(): void
    {
        config()->set('services.xendit.webhook_token', 'valid-token');

        $response = $this->postJson('/xendit/webhook', [
            'status' => 'PAID',
            'external_id' => 'BVL-ABC123',
        ], [
            'x-callback-token' => 'valid-token',
        ]);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Missing invoice id']);
    }
}
