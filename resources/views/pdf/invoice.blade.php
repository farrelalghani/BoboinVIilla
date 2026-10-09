<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice #{{ $booking->id }} — Boboin Villa</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 13px;
            color: #1f2937;
            background: #ffffff;
            padding: 40px;
        }

        /* Header */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 36px;
            border-bottom: 2px solid #3a6484;
            padding-bottom: 20px;
        }
        .header-left  { display: table-cell; vertical-align: top; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }

        .brand-name  { font-size: 22px; font-weight: bold; color: #3a6484; }
        .brand-sub   { font-size: 11px; color: #6b7280; margin-top: 2px; }

        .invoice-title  { font-size: 22px; font-weight: bold; color: #1f2937; }
        .invoice-number { font-size: 12px; color: #6b7280; margin-top: 4px; }
        .invoice-date   { font-size: 12px; color: #6b7280; margin-top: 2px; }

        /* Status badge */
        .badge-paid {
            display: inline-block;
            margin-top: 8px;
            padding: 3px 10px;
            background: #dcfce7;
            color: #166534;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }

        /* Info section */
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 28px;
        }
        .info-box {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
        }
        .info-spacer { display: table-cell; width: 4%; }
        .info-label  { font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; margin-bottom: 8px; font-weight: bold; }
        .info-value  { font-size: 13px; color: #1f2937; margin-bottom: 3px; }
        .info-sub    { font-size: 11px; color: #6b7280; }

        /* Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .items-table thead tr {
            background: #3a6484;
            color: #ffffff;
        }
        .items-table thead th {
            padding: 10px 14px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .items-table thead th:last-child { text-align: right; }
        .items-table tbody tr { border-bottom: 1px solid #f3f4f6; }
        .items-table tbody tr:last-child { border-bottom: none; }
        .items-table tbody td {
            padding: 12px 14px;
            font-size: 13px;
            color: #374151;
        }
        .items-table tbody td:last-child { text-align: right; font-weight: 600; }

        /* Total */
        .total-box {
            float: right;
            width: 280px;
            margin-bottom: 32px;
        }
        .total-row {
            display: table;
            width: 100%;
            padding: 5px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .total-label { display: table-cell; color: #6b7280; font-size: 12px; }
        .total-value { display: table-cell; text-align: right; font-size: 12px; color: #1f2937; }
        .total-final .total-label,
        .total-final .total-value {
            font-size: 15px;
            font-weight: bold;
            color: #3a6484;
            padding-top: 10px;
            border-bottom: none;
        }

        .clearfix::after { content: ''; display: table; clear: both; }

        /* Payment info */
        .payment-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 28px;
            clear: both;
        }
        .payment-box .info-label { color: #1e40af; }
        .payment-row { display: table; width: 100%; margin-top: 6px; }
        .payment-key { display: table-cell; color: #374151; font-size: 12px; width: 140px; }
        .payment-val { display: table-cell; color: #1e3a8a; font-size: 12px; font-weight: 600; }

        /* Footer */
        .footer {
            border-top: 1px solid #e5e7eb;
            padding-top: 16px;
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
        }
        .footer strong { color: #3a6484; }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            <div class="brand-name">Boboin Villa</div>
            <div class="brand-sub">Platform Pemesanan Villa Terpercaya</div>
        </div>
        <div class="header-right">
            <div class="invoice-title">INVOICE</div>
            <div class="invoice-number">#INV-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</div>
            <div class="invoice-date">Tanggal: {{ now()->format('d F Y') }}</div>
            <div><span class="badge-paid">&#10003; LUNAS</span></div>
        </div>
    </div>

    {{-- Info Tamu & Villa --}}
    <div class="info-row">
        <div class="info-box">
            <div class="info-label">Tagihan Kepada</div>
            <div class="info-value" style="font-weight:600;">{{ $booking->guest_name }}</div>
            <div class="info-sub">{{ $booking->guest_email }}</div>
            @if($booking->guest_phone)
                <div class="info-sub">{{ $booking->guest_phone }}</div>
            @endif
            @if($booking->guest_city)
                <div class="info-sub">{{ $booking->guest_city }}</div>
            @endif
            @if($booking->adult_count !== null)
                <div class="info-sub" style="margin-top:4px;">
                    Tamu: {{ $booking->adult_count }} dewasa
                    @if($booking->child_count) · {{ $booking->child_count }} anak @endif
                </div>
            @endif
        </div>
        <div class="info-spacer"></div>
        <div class="info-box">
            <div class="info-label">Detail Pemesanan</div>
            <div class="info-value" style="font-weight:600;">{{ $booking->villa->name }}</div>
            <div class="info-sub">{{ $booking->villa->city }}</div>
            <div class="info-sub" style="margin-top:6px;">
                Check-in&nbsp;&nbsp;: {{ $booking->check_in->format('d M Y') }}<br>
                Check-out : {{ $booking->check_out->format('d M Y') }}<br>
                Durasi&nbsp;&nbsp;&nbsp;&nbsp;: {{ $booking->check_in->diffInDays($booking->check_out) }} malam
            </div>
        </div>
    </div>

    {{-- Tabel Item --}}
    <table class="items-table">
        <thead>
            <tr>
                <th>Deskripsi</th>
                <th>Durasi</th>
                <th>Rata-rata / Malam</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $booking->villa->name }}</strong><br>
                    <span style="font-size:11px;color:#6b7280;">{{ $booking->villa->address }}</span>
                </td>
                @php $nights = $booking->check_in->diffInDays($booking->check_out); @endphp
                <td>{{ $nights }} malam</td>
                <td>Rp {{ number_format($nights ? $booking->total_price / $nights : 0, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Total --}}
    <div class="clearfix">
        <div class="total-box">
            <div class="total-row">
                <span class="total-label">Subtotal</span>
                <span class="total-value">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
            </div>
            <div class="total-row">
                <span class="total-label">Biaya Layanan</span>
                <span class="total-value">Rp 0</span>
            </div>
            <div class="total-row total-final">
                <span class="total-label">Total Pembayaran</span>
                <span class="total-value">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- Info Pembayaran --}}
    @if($booking->payment)
        <div class="payment-box">
            <div class="info-label">Informasi Pembayaran</div>
            @if($booking->payment->payment_type === 'xendit')
                <div class="payment-row">
                    <span class="payment-key">Metode</span>
                    <span class="payment-val">Xendit Payment Gateway</span>
                </div>
                @if($booking->payment->xendit_invoice_id)
                    <div class="payment-row">
                        <span class="payment-key">ID Invoice</span>
                        <span class="payment-val">{{ $booking->payment->xendit_invoice_id }}</span>
                    </div>
                @endif
            @else
                <div class="payment-row">
                    <span class="payment-key">Metode</span>
                    <span class="payment-val">Transfer Bank</span>
                </div>
                <div class="payment-row">
                    <span class="payment-key">Bank</span>
                    <span class="payment-val">{{ strtoupper($booking->payment->bank ?? '-') }}</span>
                </div>
                <div class="payment-row">
                    <span class="payment-key">No. Rekening</span>
                    <span class="payment-val">{{ $booking->payment->account_number ?? '-' }}</span>
                </div>
            @endif
            <div class="payment-row">
                <span class="payment-key">Status</span>
                <span class="payment-val">Terverifikasi &#10003;</span>
            </div>
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p>Terima kasih telah mempercayai <strong>Boboin Villa</strong> untuk perjalanan Anda.</p>
        <p style="margin-top:4px;">Dokumen ini digenerate secara otomatis dan sah tanpa tanda tangan.</p>
    </div>

</body>
</html>
