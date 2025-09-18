<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case OPERATOR = 'operator';
    case USER = 'user';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::OPERATOR => 'Operator',
            self::USER => 'User',
        };
    }

    public function permissions(): array
    {
        return match($this) {
            self::ADMIN => [
                'events.create',
                'events.update',
                'events.delete',
                'events.view',
                'bookings.view',
                'tickets.view',
                'users.manage',
            ],
            self::OPERATOR => [
                'tickets.scan',
                'tickets.validate',
                'events.view',
            ],
            self::USER => [
                'events.view',
                'bookings.create',
                'bookings.view.own',
                'tickets.view.own',
            ],
        };
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions());
    }
}
