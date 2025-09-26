<?php

namespace App\Providers;

use App\Models\Ticket;
use App\Models\Booking;
use App\Policies\TicketPolicy;
use App\Policies\BookingPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(Booking::class, BookingPolicy::class);

        // Use Bootstrap pagination views consistently (avoid Tailwind default duplication)
        Paginator::useBootstrapFive();
    }
}
