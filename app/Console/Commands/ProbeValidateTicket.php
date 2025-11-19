<?php

namespace App\Console\Commands;

use App\Http\Controllers\TicketController;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class ProbeValidateTicket extends Command
{
    protected $signature = 'probe:validate-ticket {qr} {operator_id}';

    protected $description = 'Probe command to validate a ticket via the controller, intended for concurrency tests';

    public function handle(): int
    {
        try {
            $qr = $this->argument('qr');
            $operatorId = (int)$this->argument('operator_id');

            /** @var User|null $operator */
            $operator = User::find($operatorId);
            if (!$operator) {
                $this->line(json_encode(['success' => false, 'status' => 'error', 'message' => 'Operator not found']));
                return self::SUCCESS;
            }

            Auth::login($operator);

            /** @var TicketController $controller */
            $controller = app(TicketController::class);
            $response = $controller->validateTicket($qr);

            $content = method_exists($response, 'getContent') ? $response->getContent() : (string)$response;
            $this->line($content);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->line(json_encode(['success' => false, 'status' => 'error', 'message' => $e->getMessage()]));
            return self::SUCCESS;
        }
    }
}
