<?php

namespace App\Exports;

use App\Models\Event;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EventsBulkExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    /** @var Collection<int, Event> */
    protected Collection $events;

    /** @param Collection<int, Event> $events */
    public function __construct(Collection $events)
    {
        $this->events = $events;
    }

    public function collection(): Collection
    {
        return $this->events;
    }

    public function headings(): array
    {
        return [
            'Event ID', 'Event Name', 'Event Type', 'Status', 'Event Date', 'Event Time', 'Location', 'Promoted',
            'Capacity', 'Available Seats', 'Bookings Count', 'Tickets Sold', 'Tickets Scanned', 'Tickets Unscanned', 'Total Revenue (EGP)'
        ];
    }

    /** @param Event $event */
    public function map($event): array
    {
        $bookingsCount = $event->bookings()->count();
        $ticketsSold = $event->tickets()->count();
        $ticketsScanned = $event->tickets()->whereNotNull('scanned_at')->count();
        $ticketsUnscanned = max(0, $ticketsSold - $ticketsScanned);
        $totalRevenue = (float) $event->bookings()->sum('total_amount');
        $availableSeats = $event->getAvailableSeatsAttribute();

        return [
            $event->id,
            $event->title,
            $event->type,
            $event->status->value,
            optional($event->event_date)->toDateString(),
            optional($event->event_time)->format('H:i'),
            $event->location,
            $event->is_featured ? 'Yes' : 'No',
            (int) $event->capacity,
            (int) $availableSeats,
            (int) $bookingsCount,
            (int) $ticketsSold,
            (int) $ticketsScanned,
            (int) $ticketsUnscanned,
            $totalRevenue,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Bold header row
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
