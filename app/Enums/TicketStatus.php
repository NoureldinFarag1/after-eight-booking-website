<?php

namespace App\Enums;

enum TicketStatus: string
{
    case VALID = 'valid';
    case USED = 'used';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match($this) {
            self::VALID => 'Valid',
            self::USED => 'Used',
            self::CANCELLED => 'Cancelled',
            self::EXPIRED => 'Expired',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::VALID => 'green',
            self::USED => 'blue',
            self::CANCELLED => 'red',
            self::EXPIRED => 'gray',
        };
    }

    public function canBeScanned(): bool
    {
        return $this === self::VALID;
    }
}
