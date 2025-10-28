<?php

namespace App\Exports\Sheets;

use App\Models\Event;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class EventKpiSheet implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting, WithTitle
{
    protected Event $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function title(): string
    {
        return 'KPIs';
    }

    public function headings(): array
    {
        return [
            'Event ID', 'Event Name', 'Event Type', 'Status', 'Event Date', 'Event Time', 'Location', 'Promoted',
            'Capacity', 'Available Seats', 'Bookings Count', 'Tickets Sold', 'Tickets Scanned', 'Tickets Unscanned', 'Total Revenue (EGP)'
        ];
    }

    public function array(): array
    {
        $bookingsCount = $this->event->bookings()->count();
        $ticketsSold = $this->event->tickets()->count();
        $ticketsScanned = $this->event->tickets()->whereNotNull('scanned_at')->count();
        $ticketsUnscanned = max(0, $ticketsSold - $ticketsScanned);
        $totalRevenue = (float) $this->event->bookings()->sum('total_amount');
        $availableSeats = $this->event->getAvailableSeatsAttribute();

        return [[
            $this->event->id,
            $this->event->title,
            $this->event->type,
            $this->event->status->value,
            optional($this->event->event_date)->toDateString(),
            optional($this->event->event_time)->format('H:i'),
            $this->event->location,
            $this->event->is_featured ? 'Yes' : 'No',
            (int) $this->event->capacity,
            (int) $availableSeats,
            (int) $bookingsCount,
            (int) $ticketsSold,
            (int) $ticketsScanned,
            (int) $ticketsUnscanned,
            $totalRevenue,
        ]];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnFormats(): array
    {
        // Column O is Total Revenue
        return [
            'O' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }
}
