<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class NewPasswordController extends Controller
{
    public function create(Request $request)
    {
        return view('auth.reset-password', [
            'token' => $request->route('token'),
            'email' => $request->query('email') ?? $request->input('email')
        ]);
    }

    public function store(Request $request)
    {
        $email = (string) $request->input('email', '');
        $emailLocal = strtolower((string) substr($email, 0, (int) strpos($email.'@','@')));

        $pwRule = PasswordRule::min(12)->mixedCase()->letters()->numbers()->symbols();
        if (app()->environment('production')) {
            $pwRule = $pwRule->uncompromised();
        }

        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'confirmed',
                // Match strong policy used on registration
                $pwRule,
                'regex:/^\S+$/',
                function($attribute, $value, $fail) use ($emailLocal) {
                    $v = strtolower((string) $value);
                    if ($emailLocal && strlen($emailLocal) >= 3 && str_contains($v, $emailLocal)) {
                        return $fail('The password must not contain parts of your email address.');
                    }
                    if (preg_match('/(.)\\1{3,}/', (string) $value)) {
                        return $fail('The password must not contain 4 or more repeating characters.');
                    }
                    $seqs = ['0123456789','abcdefghijklmnopqrstuvwxyz','qwertyuiop','asdfghjkl','zxcvbnm'];
                    foreach ($seqs as $seq) {
                        for ($i=0; $i <= strlen($seq)-5; $i++) {
                            $chunk = substr($seq, $i, 5);
                            if (str_contains($v, $chunk)) {
                                return $fail('The password contains an obvious sequence (e.g., '.$chunk.').');
                            }
                        }
                    }
                }
            ]
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status == Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
