<?php

namespace App\Exports\Sheets;

use App\Models\Event;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BookingsSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting, WithTitle
{
    protected Event $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function title(): string
    {
        return 'Bookings';
    }

    public function collection()
    {
        return $this->event->bookings()->with('user')->get();
    }

    public function headings(): array
    {
        return [
            'Booking ID', 'User Name', 'User Email', 'Quantity', 'Total Amount (EGP)', 'Status', 'Created At'
        ];
    }

    public function map($booking): array
    {
        return [
            $booking->id,
            optional($booking->user)->name ?? '—',
            optional($booking->user)->email ?? '—',
            (int)($booking->quantity ?? 0),
            (float)($booking->total_amount ?? 0),
            $booking->status->value ?? ($booking->status ?? 'N/A'),
            optional($booking->created_at)->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnFormats(): array
    {
        // Column E is Total Amount
        return [
            'E' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }
}
