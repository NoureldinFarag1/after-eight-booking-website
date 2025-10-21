<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{

    /*
     |--------------------------------------------------------------------------
     | Authentication Controller
     |--------------------------------------------------------------------------
     | Handles classic email/password auth plus Google OAuth (Socialite).
     | Buttons on the login / register views point to route('auth.google.redirect')
     | which maps here -> redirectToGoogle(). After Google's callback the
     | handleGoogleCallback method performs safe linking / creation wrapped
     | in a transaction and then applies the same role-based redirect logic
     | used in password login for consistency.
     */

    /**
     * Show the login form.
     */
    public function showLogin()
    {
        // If user navigated here from another page, remember it to return after login
        // Only set if not already set by the auth middleware (so protected routes keep priority)
        $request = request();
        if (!$request->session()->has('url.intended')) {
            $previous = url()->previous();
            $current  = url()->current();
            // Avoid loops and non-useful pages
            $blocked = [route('login', [], false), route('register', [], false)];
            $isBlocked = in_array(parse_url($previous, PHP_URL_PATH), array_map(function($u){ return parse_url($u, PHP_URL_PATH); }, $blocked), true);
            if ($previous && $previous !== $current && !$isBlocked) {
                // Store absolute URL so intended works cross-domain if needed
                $request->session()->put('url.intended', $previous);
            }
        }
        return view('auth.login');
    }

    /**
     * Handle login attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Role-based redirects
            if ($user->role === Role::OPERATOR) {
                // Block inactive operators
                if (!$user->active) {
                    Auth::logout();
                    return back()->withErrors(['email' => 'Your operator account is inactive. Please contact an administrator.']);
                }
                // Operators go to scan
                return redirect()->route('tickets.scan')
                    ->with('success', 'Welcome back, ' . $user->name . '!');
            }


            // Approval officer redirect (also block if inactive)
            if ($user->role === Role::APPROVAL_OFFICER) {
                if (!$user->active) {
                    Auth::logout();
                    return back()->withErrors(['email' => 'Your approval officer account is inactive. Please contact an administrator.']);
                }
                return redirect()->route('approval.index')
                    ->with('success', 'Welcome back, ' . $user->name . '!');
            }

            // Finance officer redirect (also block if inactive)
            if ($user->role === Role::FINANCE_OFFICER) {
                if (!$user->active) {
                    Auth::logout();
                    return back()->withErrors(['email' => 'Your finance officer account is inactive. Please contact an administrator.']);
                }
                return redirect()->route('finance.insights')
                    ->with('success', 'Welcome back, ' . $user->name . '!');
            }

            if ($user->role === Role::ADMIN) {
                // Admins go to dashboard
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Welcome back, ' . $user->name . '!');
            }

            // Regular users - also check if they are active
            if (!$user->active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Your account has been deactivated. Please contact support for assistance.']);
            }

            // Regular users: return to intended page (if any) else go to welcome (home)
            return redirect()->intended(route('home'))
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show the registration form.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle registration.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'birthday' => ['required', 'date', 'before:' . now()->subYears(13)->format('Y-m-d')],
            'gender' => ['required', 'in:male,female'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'birthday' => $request->birthday,
            'gender' => $request->gender,
            'password' => Hash::make($request->password),
            'role' => Role::USER, // Default role
            'profile_completed' => true, // Registration form provides all required info
        ]);

        Auth::login($user);
        // After registration, also honor intended URL (e.g., user started from an event page)
        return redirect()->intended(route('home'))->with('success', 'Welcome to After Eight Events, ' . $user->name . '!');
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

    // Use a low-priority info flag instead of a prominent success toast
    return redirect()->route('home')->with('info', 'You have been logged out.');
    }

    /**
     * Redirect to Google for authentication.
     */
    public function redirectToGoogle(Request $request)
    {
        // Standard redirect (stateful) so CSRF/state is validated.
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback.
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::warning('Google OAuth failed', ['error' => $e->getMessage()]);
            return redirect()->route('login')->withErrors(['email' => 'Google authentication failed. Please try again.']);
        }

        // Normalize provider data
        $providerName = 'google';
        $providerId   = $googleUser->getId();
        $email        = $googleUser->getEmail();
        $name         = $googleUser->getName() ?? $googleUser->getNickname() ?? 'User';
        $token        = $googleUser->token ?? null;
        $refreshToken = $googleUser->refreshToken ?? null;

        // Transaction for safe create/update
        $user = DB::transaction(function () use ($providerName, $providerId, $email, $name, $token, $refreshToken) {
            // 1. Try matching by provider composite first (fast indexed lookup)
            $found = User::where('provider_name', $providerName)
                ->where('provider_id', $providerId)
                ->first();
            if ($found) {
                // Update tokens silently (avoid unnecessary writes if unchanged)
                $dirty = false;
                if ($token && $found->provider_token !== $token) { $found->provider_token = $token; $dirty = true; }
                if ($refreshToken && $found->provider_refresh_token !== $refreshToken) { $found->provider_refresh_token = $refreshToken; $dirty = true; }
                if ($dirty) { $found->save(); }
                return $found;
            }

            // 2. If not, match by email to link existing password user (avoid duplicates)
            $foundByEmail = $email ? User::where('email', $email)->first() : null;
            if ($foundByEmail) {
                // Only link if provider not already linked elsewhere
                $foundByEmail->provider_name = $providerName;
                $foundByEmail->provider_id = $providerId;
                if ($token) { $foundByEmail->provider_token = $token; }
                if ($refreshToken) { $foundByEmail->provider_refresh_token = $refreshToken; }
                $foundByEmail->save();
                return $foundByEmail;
            }

            // 3. Create new user with default role USER
            return User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(str()->random(32)), // random placeholder; user may set password later
                'role' => Role::USER,
                'active' => true,
                'profile_completed' => false, // Google OAuth users need to complete profile
                'provider_name' => $providerName,
                'provider_id' => $providerId,
                'provider_token' => $token,
                'provider_refresh_token' => $refreshToken,
            ]);
        });

        // Check if user is active before logging them in
        if (!$user->active) {
            return redirect()->route('login')->withErrors(['email' => 'Your account has been deactivated. Please contact support for assistance.']);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        // Role-based redirect consistent with password login
        if ($user->role === Role::OPERATOR) {
            return redirect()->route('tickets.scan')->with('success', 'Welcome back, ' . $user->name . '!');
        }

        // Approval officer redirect
        if ($user->role === Role::APPROVAL_OFFICER) {
            return redirect()->route('approval.index')
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        // Finance officer redirect
        if ($user->role === Role::FINANCE_OFFICER) {
            return redirect()->route('finance.insights')
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        if ($user->role === Role::ADMIN) {
            return redirect()->intended(route('admin.dashboard'))->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return redirect()->intended(route('home'))->with('success', 'Welcome back, ' . $user->name . '!');
    }
}
