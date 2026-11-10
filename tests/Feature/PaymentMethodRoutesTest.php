<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PaymentMethodRoutesTest extends TestCase
{
    public function test_manual_receipt_upload_is_disabled_and_xendit_route_remains_available(): void
    {
        $this->assertFalse(Route::has('guest.payment.upload.post'));
        $this->assertTrue(Route::has('guest.payment.xendit.post'));

        $route = Route::getRoutes()->getByName('guest.payment.xendit.post');

        $this->assertContains('throttle:5,1', $route->gatherMiddleware());
    }
}