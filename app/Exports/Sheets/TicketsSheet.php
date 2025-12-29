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

class TicketsSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting, WithTitle
{
    protected Event $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function title(): string
    {
        return 'Tickets';
    }

    public function collection()
    {
        return $this->event->tickets()->with(['booking.user'])->get();
    }

    public function headings(): array
    {
        return [
            'Ticket ID', 'Price (EGP)', 'Status', 'Scanned At', 'Booking ID', 'Buyer Name', 'Buyer Email'
        ];
    }

    public function map($ticket): array
    {
        return [
            $ticket->id,
            (float)($ticket->price ?? 0),
            $ticket->status->value ?? ($ticket->status ?? 'N/A'),
            optional($ticket->scanned_at)->format('Y-m-d H:i:s'),
            $ticket->booking_id ?? optional($ticket->booking)->id,
            optional(optional($ticket->booking)->user)->name ?? '—',
            optional(optional($ticket->booking)->user)->email ?? '—',
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
        // Column B is Price
        return [
            'B' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }
}
