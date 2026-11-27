<?php

namespace Tests\Feature;

use App\Mail\BookingPaymentRejected;
use App\Mail\BookingPaymentVerified;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\Villa;
use App\Services\Payment\ManualPaymentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ManualPaymentSecurityTest extends TestCase
{
    private Villa $villa;
    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareInMemoryTables();
        Storage::fake('local');
        Mail::fake();

        SiteSetting::set('payment_manual_bank', 'BCA');
        SiteSetting::set('payment_manual_account_number', '8800112233');
        SiteSetting::set('payment_manual_account_name', 'PT Boboin Villa');

        $this->villa = Villa::create([
            'name'          => 'Villa Dago Test',
            'slug'          => 'villa-dago-test',
            'description'   => 'Villa uji coba',
            'weekday_price' => 2000000,
            'weekend_price' => 2500000,
            'capacity'      => 6,
            'city'          => 'Bandung',
            'address'       => 'Jl. Dago Pakar No. 1',
            'status'        => 'active',
        ]);

        $this->booking = Booking::create([
            'booking_code' => 'BVL-TEST01',
            'villa_id'     => $this->villa->id,
            'check_in'     => now()->addDays(5)->toDateString(),
            'check_out'    => now()->addDays(7)->toDateString(),
            'total_price'  => 4500000,
            'status'       => 'approved',
        ]);
    }

    private function prepareInMemoryTables(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('villas');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('villas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('weekday_price', 12, 2);
            $table->decimal('weekend_price', 12, 2);
            $table->integer('capacity');
            $table->string('city');
            $table->string('address');
            $table->string('status')->default('active');
            $table->string('image')->nullable();
            $table->json('images')->nullable();
            $table->json('facilities')->nullable();
            $table->json('nearby_destinations')->nullable();
            $table->json('house_rules')->nullable();
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 20)->unique()->nullable();
            $table->unsignedBigInteger('guest_id')->nullable();
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('guest_phone', 20)->nullable();
            $table->string('guest_city')->nullable();
            $table->unsignedInteger('adult_count')->nullable();
            $table->unsignedInteger('child_count')->nullable();
            $table->unsignedBigInteger('villa_id');
            $table->date('check_in');
            $table->date('check_out');
            $table->decimal('total_price', 12, 2);
            $table->string('status')->default('waiting_admin');
            $table->text('rejection_note')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->string('payment_type')->nullable();
            $table->string('bank')->nullable();
            $table->string('account_number')->nullable();
            $table->string('receipt_image')->nullable();
            $table->decimal('gross_amount', 12, 2);
            $table->string('payment_status')->default('pending');
            $table->string('xendit_invoice_id')->nullable();
            $table->text('xendit_invoice_url')->nullable();
            $table->timestamps();
        });
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'guest_name'  => 'Budi Santoso',
            'guest_phone' => '081234567890',
            'guest_email' => 'budi@example.com',
            'guest_city'  => 'Jakarta',
            'adult_count' => 4,
            'child_count' => 1,
            'receipt'     => UploadedFile::fake()->image('bukti-transfer.jpg', 600, 800),
        ], $overrides);
    }

    public function test_guest_can_upload_valid_manual_transfer_receipt_to_private_storage(): void
    {
        $response = $this->post('/payment/BVL-TEST01/upload', $this->validPayload());

        $response->assertRedirect(route('guest.payment.upload', 'BVL-TEST01'))
            ->assertSessionHas('success');

        $booking = $this->booking->fresh(['payment']);
        $this->assertSame('pending_payment', $booking->status);
        $this->assertSame('Budi Santoso', $booking->guest_name);
        $this->assertNotNull($booking->payment);
        $this->assertSame('manual_transfer', $booking->payment->payment_type);
        $this->assertSame('BCA', $booking->payment->bank);
        $this->assertSame('8800112233', $booking->payment->account_number);
        $this->assertSame('uploaded', $booking->payment->payment_status);

        Storage::disk('local')->assertExists($booking->payment->receipt_image);
    }

    public function test_fake_php_or_disguised_file_is_rejected_by_mime_and_content_checks(): void
    {
        $disguisedPhp = UploadedFile::fake()->createWithContent(
            'shell.jpg',
            "<?php system(\$_GET['cmd']); ?>"
        );

        $response = $this->post('/payment/BVL-TEST01/upload', $this->validPayload([
            'receipt' => $disguisedPhp,
        ]));

        $response->assertSessionHasErrors('receipt');
        $this->assertSame('approved', $this->booking->fresh()->status);
        $this->assertCount(0, Storage::disk('local')->allFiles('receipts'));

        $fakePdf = UploadedFile::fake()->createWithContent(
            'nota.pdf',
            'Not actually a PDF header'
        );

        $pdfResponse = $this->post('/payment/BVL-TEST01/upload', $this->validPayload([
            'receipt' => $fakePdf,
        ]));

        $pdfResponse->assertSessionHasErrors('receipt');
        $this->assertCount(0, Storage::disk('local')->allFiles('receipts'));
    }

    public function test_guest_cannot_upload_when_booking_is_not_approved_or_exceeds_capacity(): void
    {
        $overflowResponse = $this->post('/payment/BVL-TEST01/upload', $this->validPayload([
            'adult_count' => 5,
            'child_count' => 3, // capacity = 6
        ]));

        $overflowResponse->assertSessionHasErrors('adult_count');

        $this->booking->update(['status' => 'waiting_admin']);
        $this->post('/payment/BVL-TEST01/upload', $this->validPayload())->assertStatus(404);

        $this->booking->update(['status' => 'paid']);
        $this->post('/payment/BVL-TEST01/upload', $this->validPayload())->assertStatus(404);
    }

    public function test_manual_upload_route_enforces_rate_limiting(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/payment/BVL-TEST01/upload', ['guest_name' => 'Test']);
        }

        $this->post('/payment/BVL-TEST01/upload', $this->validPayload())
            ->assertStatus(429);
    }

    public function test_verify_and_reject_transitions_are_atomic_idempotent_and_clean_old_receipts(): void
    {
        $service = new ManualPaymentService();

        $this->post('/payment/BVL-TEST01/upload', $this->validPayload())->assertRedirect();
        $payment = $this->booking->fresh(['payment'])->payment;
        $firstPath = $payment->receipt_image;

        // Admin tolak struk
        $this->assertTrue($service->rejectPayment($payment, 'Nominal kurang'));
        Mail::assertSent(BookingPaymentRejected::class, 1);

        $this->assertSame('rejected', $payment->fresh()->payment_status);
        $this->assertSame('approved', $this->booking->fresh()->status);

        // Tamu unggah ulang dengan file PDF valid
        $validPdf = UploadedFile::fake()->createWithContent(
            'transfer-ulang.pdf',
            "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF"
        );

        $this->post('/payment/BVL-TEST01/upload', $this->validPayload([
            'receipt' => $validPdf,
        ]))->assertRedirect()->assertSessionHas('success');

        $this->assertSame(1, Payment::where('booking_id', $this->booking->id)->count());
        $updatedPayment = $payment->fresh();
        $this->assertSame('uploaded', $updatedPayment->payment_status);
        Storage::disk('local')->assertMissing($firstPath);
        Storage::disk('local')->assertExists($updatedPayment->receipt_image);

        // Admin verifikasi pembayaran
        $this->assertTrue($service->verifyPayment($updatedPayment));
        Mail::assertSent(BookingPaymentVerified::class, 1);
        $this->assertSame('verified', $updatedPayment->fresh()->payment_status);
        $this->assertSame('paid', $this->booking->fresh()->status);

        // Double-verify tidak mengirim email kedua atau mengembalikan true lagi
        $this->assertFalse($service->verifyPayment($updatedPayment));
        Mail::assertSent(BookingPaymentVerified::class, 1);
    }

    public function test_admin_can_download_and_safely_preview_receipt_with_nosniff_header(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@boboinvilla.my.id',
            'password' => bcrypt('secret-password'),
        ]);
        $admin->assignRole('admin');

        $this->post('/payment/BVL-TEST01/upload', $this->validPayload())->assertRedirect();
        $payment = $this->booking->fresh(['payment'])->payment;

        $download = $this->actingAs($admin)->get(route('admin.payments.receipt', $payment));
        $download->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        $preview = $this->actingAs($admin)->get(route('admin.payments.receipt', ['payment' => $payment, 'preview' => 1]));
        $preview->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Content-Security-Policy', "default-src 'none'; sandbox");
    }
}
