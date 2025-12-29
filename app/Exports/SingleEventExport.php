<?php

namespace App\Exports;

use App\Models\Event;
use App\Exports\Sheets\EventKpiSheet;
use App\Exports\Sheets\BookingsSheet;
use App\Exports\Sheets\TicketsSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SingleEventExport implements WithMultipleSheets
{
    protected Event $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function sheets(): array
    {
        return [
            new EventKpiSheet($this->event),
            new BookingsSheet($this->event),
            new TicketsSheet($this->event),
        ];
    }
}
