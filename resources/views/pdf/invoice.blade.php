<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice #{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }} ({{ $booking->booking_code }}) — Boboin Villa</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11.5px;
            line-height: 1.5;
            color: #1f2937;
            background: #ffffff;
            padding: 36px 40px;
        }

        /* Header */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 26px;
            border-bottom: 2.5px solid #3a6484;
            padding-bottom: 18px;
        }
        .header-left  { display: table-cell; vertical-align: middle; width: 58%; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; width: 42%; }

        .brand-wrap   { display: table; }
        .brand-logo-cell { display: table-cell; vertical-align: middle; padding-right: 12px; }
        .brand-text-cell { display: table-cell; vertical-align: middle; }
        .brand-logo   { width: 44px; height: 44px; border-radius: 8px; border: 1px solid #e5e7eb; }
        .brand-name   { font-size: 20px; font-weight: bold; color: #3a6484; line-height: 1.15; }
        .brand-sub    { font-size: 10px; color: #6b7280; margin-top: 2px; }

        .invoice-title  { font-size: 20px; font-weight: bold; color: #111827; letter-spacing: 0.04em; }
        .invoice-meta   { font-size: 10.5px; color: #4b5563; margin-top: 3px; }
        .booking-pill   {
            display: inline-block;
            margin-top: 6px;
            padding: 3px 10px;
            background: #eff6ff;
            color: #1e3a8a;
            border: 1px solid #bfdbfe;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.05em;
        }
        .badge-paid {
            display: inline-block;
            margin-top: 6px;
            margin-left: 4px;
            padding: 3px 10px;
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }

        /* Info section */
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 24px;
        }
        .info-box {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px 16px;
        }
        .info-spacer { display: table-cell; width: 4%; }
        .info-label  {
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6b7280;
            margin-bottom: 6px;
            font-weight: bold;
        }
        .info-value  { font-size: 12.5px; font-weight: bold; color: #111827; margin-bottom: 3px; }
        .info-sub    { font-size: 10.5px; color: #4b5563; line-height: 1.55; }

        /* Detail Table inside box */
        .mini-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .mini-table td { font-size: 10.5px; color: #4b5563; padding: 1.5px 0; vertical-align: top; }
        .mini-table td.lbl { width: 72px; color: #6b7280; }
        .mini-table td.sep { width: 10px; color: #6b7280; }
        .mini-table td.val { color: #111827; font-weight: bold; }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }
        .items-table thead tr {
            background: #3a6484;
            color: #ffffff;
        }
        .items-table thead th {
            padding: 10px 12px;
            text-align: left;
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .items-table thead th.col-dur  { width: 14%; text-align: center; }
        .items-table thead th.col-rate { width: 22%; text-align: right; }
        .items-table thead th.col-sub  { width: 21%; text-align: right; }
        .items-table thead th.col-desc { width: 43%; }

        .items-table tbody tr { border-bottom: 1px solid #e5e7eb; }
        .items-table tbody td {
            padding: 12px;
            font-size: 11.5px;
            color: #374151;
            vertical-align: top;
        }
        .items-table tbody td.col-dur  { text-align: center; white-space: nowrap; }
        .items-table tbody td.col-rate { text-align: right; white-space: nowrap; }
        .items-table tbody td.col-sub  { text-align: right; font-weight: bold; color: #111827; white-space: nowrap; }

        /* Total */
        .total-box {
            float: right;
            width: 270px;
            margin-bottom: 26px;
        }
        .total-table { width: 100%; border-collapse: collapse; }
        .total-table td {
            padding: 5px 0;
            font-size: 11px;
            border-bottom: 1px solid #f3f4f6;
        }
        .total-table td.t-lbl { color: #6b7280; }
        .total-table td.t-val { text-align: right; color: #1f2937; white-space: nowrap; }
        .total-table tr.t-final td {
            font-size: 13.5px;
            font-weight: bold;
            color: #3a6484;
            padding-top: 9px;
            border-top: 1.5px solid #cbd5e1;
            border-bottom: none;
        }

        .clearfix::after { content: ''; display: table; clear: both; }

        /* Payment info */
        .payment-box {
            background: #f0f6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 26px;
            clear: both;
        }
        .payment-box .info-label { color: #1e40af; margin-bottom: 8px; }
        .pay-table { width: 100%; border-collapse: collapse; }
        .pay-table td { padding: 3px 0; font-size: 11px; vertical-align: top; }
        .pay-table td.p-key { width: 145px; color: #4b5563; }
        .pay-table td.p-val { color: #1e3a8a; font-weight: bold; }
        .pay-verified { color: #15803d; font-weight: bold; }

        /* Footer */
        .footer {
            border-top: 1px solid #e5e7eb;
            padding-top: 14px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            line-height: 1.6;
        }
        .footer strong { color: #3a6484; }
    </style>
</head>
<body>

    @php
        $logoPath = public_path('images/logo.jpg');
        $logoDataUri = file_exists($logoPath)
            ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath))
            : null;
        $nights = max(1, $booking->check_in->diffInDays($booking->check_out));
        $avgPerNight = $booking->total_price / $nights;
    @endphp

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            <div class="brand-wrap">
                @if($logoDataUri)
                    <div class="brand-logo-cell">
                        <img src="{{ $logoDataUri }}" alt="Boboin Villa" class="brand-logo">
                    </div>
                @endif
                <div class="brand-text-cell">
                    <div class="brand-name">Boboin Villa</div>
                    <div class="brand-sub">Platform Pemesanan Villa Terpercaya</div>
                </div>
            </div>
        </div>
        <div class="header-right">
            <div class="invoice-title">INVOICE</div>
            <div class="invoice-meta">#INV-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</div>
            <div class="invoice-meta">Tanggal: {{ now()->timezone(config('app.timezone', 'Asia/Jakarta'))->translatedFormat('d F Y') }}</div>
            <div>
                <span class="booking-pill">{{ $booking->booking_code }}</span>
                <span class="badge-paid">&#10003; LUNAS</span>
            </div>
        </div>
    </div>

    {{-- Info Tamu & Villa --}}
    <div class="info-row">
        <div class="info-box">
            <div class="info-label">Tagihan Kepada</div>
            <div class="info-value">{{ $booking->guest_name ?? '-' }}</div>
            <div class="info-sub">
                @if($booking->guest_email)
                    {{ $booking->guest_email }}<br>
                @endif
                @if($booking->guest_phone)
                    {{ $booking->guest_phone }}<br>
                @endif
                @if($booking->guest_city)
                    {{ $booking->guest_city }}<br>
                @endif
                @if($booking->adult_count !== null)
                    Tamu: {{ $booking->adult_count }} dewasa
                    @if($booking->child_count) &middot; {{ $booking->child_count }} anak @endif
                @endif
            </div>
        </div>
        <div class="info-spacer"></div>
        <div class="info-box">
            <div class="info-label">Detail Pemesanan</div>
            <div class="info-value">{{ $booking->villa->name }}</div>
            <div class="info-sub">{{ $booking->villa->city }}</div>
            <table class="mini-table">
                <tr>
                    <td class="lbl">Check-in</td>
                    <td class="sep">:</td>
                    <td class="val">{{ $booking->check_in->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td class="lbl">Check-out</td>
                    <td class="sep">:</td>
                    <td class="val">{{ $booking->check_out->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td class="lbl">Durasi</td>
                    <td class="sep">:</td>
                    <td class="val">{{ $nights }} malam</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Tabel Item --}}
    <table class="items-table">
        <thead>
            <tr>
                <th class="col-desc">Deskripsi</th>
                <th class="col-dur">Durasi</th>
                <th class="col-rate">Rata-rata / Malam</th>
                <th class="col-sub">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="col-desc">
                    <strong>{{ $booking->villa->name }}</strong><br>
                    <span style="font-size:10px;color:#6b7280;">{{ $booking->villa->address }}</span>
                </td>
                <td class="col-dur">{{ $nights }} malam</td>
                <td class="col-rate">Rp {{ number_format($avgPerNight, 0, ',', '.') }}</td>
                <td class="col-sub">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Total --}}
    <div class="clearfix">
        <div class="total-box">
            <table class="total-table">
                <tr>
                    <td class="t-lbl">Subtotal</td>
                    <td class="t-val">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="t-lbl">Biaya Layanan</td>
                    <td class="t-val">Rp 0</td>
                </tr>
                <tr class="t-final">
                    <td class="t-lbl">Total Pembayaran</td>
                    <td class="t-val">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Info Pembayaran --}}
    @if($booking->payment)
        <div class="payment-box">
            <div class="info-label">Informasi Pembayaran</div>
            <table class="pay-table">
                @if($booking->payment->payment_type === 'xendit')
                    <tr>
                        <td class="p-key">Metode</td>
                        <td class="p-val">Xendit Payment Gateway</td>
                    </tr>
                    @if($booking->payment->xendit_invoice_id)
                        <tr>
                            <td class="p-key">ID Invoice</td>
                            <td class="p-val">{{ $booking->payment->xendit_invoice_id }}</td>
                        </tr>
                    @endif
                @else
                    <tr>
                        <td class="p-key">Metode</td>
                        <td class="p-val">Transfer Bank Manual</td>
                    </tr>
                    <tr>
                        <td class="p-key">Bank</td>
                        <td class="p-val">{{ strtoupper($booking->payment->bank ?? '-') }}</td>
                    </tr>
                    <tr>
                        <td class="p-key">No. Rekening</td>
                        <td class="p-val">{{ $booking->payment->account_number ?? '-' }}</td>
                    </tr>
                @endif
                <tr>
                    <td class="p-key">Status</td>
                    <td><span class="pay-verified">Terverifikasi &#10003;</span></td>
                </tr>
            </table>
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p>Terima kasih telah mempercayai <strong>Boboin Villa</strong> untuk perjalanan Anda.</p>
        <p>Dokumen ini digenerate secara otomatis oleh sistem dan sah tanpa tanda tangan.</p>
    </div>

</body>
</html>
