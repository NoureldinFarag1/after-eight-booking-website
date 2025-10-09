<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EventRequest;
use App\Enums\EventRequestStatus;

class ExpireEventRequests extends Command
{
    protected $signature = 'event-requests:expire';
    protected $description = 'Expire event requests that exceeded their payment window, releasing held seats';

    public function handle(): int
    {
        $count = EventRequest::where('status', EventRequestStatus::AWAITING_PAYMENT->value)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => EventRequestStatus::EXPIRED->value]);

        $this->info("Expired {$count} event request(s).");
        return Command::SUCCESS;
    }
}
