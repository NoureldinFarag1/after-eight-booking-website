<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'age',
        'gender',
        'birthday',
        'active',
        'profile_completed',
        'provider_id',
        'provider_name',
        'provider_token',
        'provider_refresh_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'provider_token',
        'provider_refresh_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'active' => 'boolean',
            'profile_completed' => 'boolean',
            'birthday' => 'date',
            'provider_token' => 'encrypted',
            'provider_refresh_token' => 'encrypted',
        ];
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(Role $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has permission
     */
    public function hasPermission(string $permission): bool
    {
        return $this->role?->hasPermission($permission) ?? false;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === Role::ADMIN;
    }

    public function isFinanceOfficer(): bool
    {
        return $this->role === Role::FINANCE_OFFICER;
    }

    /**
     * Check if user is operator
     */
    public function isOperator(): bool
    {
        return $this->role === Role::OPERATOR;
    }

    /**
     * Check if user is approval officer
     */
    public function isApprovalOfficer(): bool
    {
        return $this->role === Role::APPROVAL_OFFICER;
    }

    /**
     * Determine if user is any staff role (manageable staff or admin).
     */
    public function isStaff(): bool
    {
        return $this->isAdmin() || ($this->role && $this->role->isManageableStaff());
    }

    /**
     * Check if user is regular user
     */
    public function isUser(): bool
    {
        return $this->role === Role::USER;
    }

    /**
     * Check if user account is active
     */
    public function isActive(): bool
    {
        return $this->active === true;
    }

    /**
     * Check if user account is inactive
     */
    public function isInactive(): bool
    {
        return $this->active === false;
    }

    /**
     * Check if user has completed their profile
     */
    public function hasCompletedProfile(): bool
    {
        return $this->profile_completed === true;
    }

    /**
     * Check if user needs to complete their profile
     */
    public function needsProfileCompletion(): bool
    {
        return !$this->profile_completed && !$this->isStaff();
    }

    /**
     * Calculate age from birthday
     */
    public function getCalculatedAge(): ?int
    {
        if (!$this->birthday) {
            return null;
        }

        return \Carbon\Carbon::parse($this->birthday)->diffInYears(now());
    }

    /**
     * Get display age (calculated from birthday or stored age)
     */
    public function getDisplayAge(): ?int
    {
        // Prioritize calculated age from birthday
        $calculatedAge = $this->getCalculatedAge();

        if ($calculatedAge !== null) {
            return $calculatedAge;
        }

        // Fallback to stored age field for legacy users
        return $this->age;
    }

    /**
     * Get bookings for this user
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get tickets for this user
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get tickets scanned by this user (for operators)
     */
    public function scannedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'scanned_by');
    }

    /**
     * Get invitations received by this user
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'email', 'email');
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}
