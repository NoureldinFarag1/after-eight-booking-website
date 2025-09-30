<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case APPROVAL_OFFICER = 'approval_officer';
    case OPERATOR = 'operator';
    case USER = 'user';
    case FINANCE_OFFICER = 'finance_officer';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::APPROVAL_OFFICER => 'Approval Officer',
            self::FINANCE_OFFICER => 'Finance Officer',
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
            self::APPROVAL_OFFICER => [
                // Can only view and act on pending event requests
                'event_requests.review',
                'event_requests.approve',
                'event_requests.reject',
            ],
            self::OPERATOR => [
                'tickets.scan',
                'tickets.validate',
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

    /**
     * Roles that are managed through the current staff management UI (formerly "operators" page).
     * Extend this list if additional staff-type roles are introduced later.
     *
     * @return Role[]
     */
    public static function manageableStaff(): array
    {
        return [self::OPERATOR, self::APPROVAL_OFFICER, self::FINANCE_OFFICER];
    }

    public function isManageableStaff(): bool
    {
        return in_array($this, self::manageableStaff(), true);
    }
}
