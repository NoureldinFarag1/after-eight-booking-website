<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EventRequest;
use App\Enums\EventRequestStatus;

class SimulateExpireEventRequests extends Command
{
    protected $signature = 'event-requests:simulate-expire {eventRequestId?} {--all : Expire all awaiting_payment requests}';
    protected $description = 'Force-expire awaiting_payment event requests immediately (testing utility)';

    public function handle(): int
    {
        $id = $this->argument('eventRequestId');
        $all = (bool) $this->option('all');

        if (!$all && !$id) {
            $this->error('Provide an eventRequestId or use --all');
            return Command::INVALID;
        }

        $query = EventRequest::where('status', EventRequestStatus::AWAITING_PAYMENT->value);
        if (!$all) {
            $query->where('id', $id);
        }

        $count = 0;
        $query->chunkById(200, function($chunk) use (&$count) {
            foreach ($chunk as $req) {
                $req->status = EventRequestStatus::EXPIRED->value;
                $req->expires_at = now()->subMinute();
                $req->save();
                $count++;
            }
        });

        $this->info("Force-expired {$count} request(s).");
        return Command::SUCCESS;
    }
}
