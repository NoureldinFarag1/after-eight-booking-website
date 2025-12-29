<?php

namespace App\Providers;

use App\Models\Ticket;
use App\Models\Booking;
use App\Policies\TicketPolicy;
use App\Policies\BookingPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Models\EventRequest;
use Illuminate\Support\Facades\Cache as FacadesCache;
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

        // Gate for managing approval requests (approve/reject event requests)
        Gate::define('approval.manage', function($user) {
            return $user && ($user->isAdmin() || $user->role === \App\Enums\Role::APPROVAL_OFFICER);
        });

        // Gate for managing staff (operators, approval officers) - admin only
        Gate::define('staff.manage', function($user) {
            return $user && $user->role === \App\Enums\Role::ADMIN;
        });

        // Currency formatting helper for EGP
        View::share('formatEGP', function($amount, $decimals = 2) {
            return 'EGP ' . number_format($amount, $decimals);
        });

        // Share cached pending approvals count (30s cache) to avoid duplicate queries in layout
        View::composer('*', function($view){
            $user = Auth::user();
            if(!$user) {
                $view->with('sharedPendingApprovals', 0);
                return;
            }
            // Limit to admin or approval officer by role property to avoid relying on dynamic helpers here
            if(property_exists($user,'role') && in_array($user->role, [\App\Enums\Role::ADMIN, \App\Enums\Role::APPROVAL_OFFICER], true)) {
                $count = Cache::remember('pending_approvals_count', 30, function(){
                    return EventRequest::where('status','pending')->count();
                });
                $view->with('sharedPendingApprovals', $count);
            } else {
                $view->with('sharedPendingApprovals', 0);
            }
        });

        // Use Bootstrap pagination views consistently (avoid Tailwind default duplication)
        Paginator::useBootstrapFive();

        // Invalidate pending approvals count cache when EventRequest records change status or are created/deleted
        EventRequest::saved(function(EventRequest $req){
            if($req->wasRecentlyCreated || $req->wasChanged('status')) {
                Cache::forget('pending_approvals_count');
            }
        });
        EventRequest::deleted(function(){
            Cache::forget('pending_approvals_count');
        });
    }
}
