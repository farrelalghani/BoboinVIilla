<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BookingsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        private int $year,
        private ?int $month = null
    ) {}

    public function collection()
    {
        $query = Booking::with(['villa'])
            ->whereYear('created_at', $this->year)
            ->whereIn('status', ['paid', 'completed'])
            ->orderBy('created_at');

        if ($this->month) {
            $query->whereMonth('created_at', $this->month);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Booking',
            'Nama Tamu',
            'Email',
            'No. HP',
            'Asal Kota',
            'Villa',
            'Check-in',
            'Check-out',
            'Durasi (malam)',
            'Dewasa',
            'Anak',
            'Total Pembayaran',
            'Status',
            'Tanggal Pesan',
        ];
    }

    public function map($booking): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $booking->booking_code,
            $booking->guest_name ?? '-',
            $booking->guest_email ?? '-',
            $booking->guest_phone ?? '-',
            $booking->guest_city ?? '-',
            $booking->villa->name,
            $booking->check_in->format('d/m/Y'),
            $booking->check_out->format('d/m/Y'),
            $booking->check_in->diffInDays($booking->check_out),
            $booking->adult_count ?? '-',
            $booking->child_count ?? '-',
            $booking->total_price,
            $booking->status === 'paid' ? 'Lunas' : 'Selesai',
            $booking->created_at->format('d/m/Y'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF3A6484']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function title(): string
    {
        if ($this->month) {
            return 'Laporan ' . \Carbon\Carbon::create($this->year, $this->month)->translatedFormat('F Y');
        }
        return 'Laporan ' . $this->year;
    }
}
