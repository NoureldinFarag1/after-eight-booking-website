<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\QrCodeService;
use Illuminate\Console\Command;

class DebugQrCodes extends Command
{
    protected $signature = 'debug:qr-codes';
    protected $description = 'Debug QR code generation and paths';

    public function handle()
    {
        $this->info('🔍 Debugging QR Code System...');

        // Get latest booking
        $booking = Booking::with(['tickets'])->latest()->first();
        if (!$booking) {
            $this->error('No bookings found!');
            return;
        }

        $this->info("Booking: {$booking->id} - {$booking->booking_reference}");
        $this->info("Tickets: {$booking->tickets->count()}");

        $qrService = app(QrCodeService::class);

        foreach ($booking->tickets as $ticket) {
            $this->info("\n--- Ticket {$ticket->id} ({$ticket->ticket_number}) ---");

            try {
                // Generate QR code
                $qrPath = $qrService->generateTicketQrCode($ticket);
                $this->info("✅ QR Path: {$qrPath}");

                // Check full path
                $fullPath = storage_path('app/public/' . $qrPath);
                $this->info("📁 Full Path: {$fullPath}");

                // Check if file exists
                if (file_exists($fullPath)) {
                    $this->info("✅ File exists");
                    $fileSize = filesize($fullPath);
                    $this->info("📊 File size: {$fileSize} bytes");

                    // Test base64 encoding
                    $base64 = base64_encode(file_get_contents($fullPath));
                    $this->info("🔗 Base64 length: " . strlen($base64) . " characters");
                    $this->info("🔗 Base64 preview: " . substr($base64, 0, 50) . "...");
                } else {
                    $this->error("❌ File does not exist");
                }

                // Check verification URL
                $verifyUrl = route('tickets.verify', [
                    'ticket' => $ticket->id,
                    'code' => $ticket->qr_code
                ]);
                $this->info("🔗 Verify URL: {$verifyUrl}");

            } catch (\Exception $e) {
                $this->error("❌ Error: " . $e->getMessage());
            }
        }

        // Check storage directory
        $qrDir = storage_path('app/public/qr-codes');
        $this->info("\n📂 QR Codes Directory: {$qrDir}");
        if (is_dir($qrDir)) {
            $files = scandir($qrDir);
            $this->info("📁 Files in directory: " . (count($files) - 2));
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $this->info("  - {$file}");
                }
            }
        } else {
            $this->error("❌ QR codes directory does not exist");
        }
    }
}
