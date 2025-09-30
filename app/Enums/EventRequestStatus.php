<?php

namespace App\Enums;

enum EventRequestStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case DECLINED = 'declined';
    case AWAITING_PAYMENT = 'awaiting_payment';
    case PAID = 'paid';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::DECLINED => 'Declined',
            self::AWAITING_PAYMENT => 'Awaiting Payment',
            self::PAID => 'Paid',
            self::EXPIRED => 'Expired',
        };
    }
}
