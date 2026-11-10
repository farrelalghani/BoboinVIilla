<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminReceiptAccessTest extends TestCase
{
    public function test_guests_cannot_access_payment_receipt_download_route(): void
    {
        $response = $this->get('/admin/payments/1/receipt');

        $response->assertRedirect('/login');
    }
}