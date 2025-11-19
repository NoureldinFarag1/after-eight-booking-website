@extends('layouts.app')
@section('title','Register')
@section('body_class','auth-body')
@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-title">
            <i data-lucide="user-plus" class="text-primary" style="font-size:1.4rem;"></i>
            Create Account
        </div>
    <div class="auth-subtitle">Join After Eight to book events and manage your tickets.</div>

        <form method="POST" action="{{ route('register') }}" class="auth-form" novalidate>
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" required>
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="birthday" class="form-label">Birthday</label>
                <input type="date" class="form-control @error('birthday') is-invalid @enderror" id="birthday" name="birthday" value="{{ old('birthday') }}" required max="{{ now()->subYears(13)->format('Y-m-d') }}">
                <div class="form-text">You must be at least 13 years old to register.</div>
                @error('birthday')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Gender</label>
                <div class="row">
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input @error('gender') is-invalid @enderror" type="radio" name="gender" id="gender_male" value="male" {{ old('gender') == 'male' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="gender_male">
                                <i data-lucide="user" class="me-1"></i>Male
                            </label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input @error('gender') is-invalid @enderror" type="radio" name="gender" id="gender_female" value="female" {{ old('gender') == 'female' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="gender_female">
                                <i data-lucide="user" class="me-1"></i>Female
                            </label>
                        </div>
                    </div>
                </div>
                @error('gender')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group password-toggle-group">
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                        <button type="button" class="btn btn-outline-primary toggle-password" data-target="#password" aria-label="Show password"><i data-lucide="eye"></i></button>
                    </div>
                    <div id="password-guidelines" class="form-text mt-2">
                        <div class="mb-1 fw-semibold">Create a strong password:</div>
                        <ul class="list-unstyled small mb-2" style="line-height:1.3;">
                            <li id="pw-rule-length" class="text-danger">• At least 12 characters</li>
                            <li id="pw-rule-mixedcase" class="text-danger">• Uppercase and lowercase letters</li>
                            <li id="pw-rule-number" class="text-danger">• At least one number</li>
                            <li id="pw-rule-symbol" class="text-danger">• At least one symbol (e.g., ! @ # $ %)</li>
                            <li id="pw-rule-nospace" class="text-danger">• No spaces</li>
                            <li id="pw-rule-personal" class="text-danger">• Don’t include your name or email</li>
                            <li id="pw-rule-seq" class="text-danger">• Avoid sequences like 12345 or abcde</li>
                            <li id="pw-rule-repeat" class="text-danger">• No 4+ identical characters in a row</li>
                        </ul>
                    </div>
                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <div class="input-group password-toggle-group">
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        <button type="button" class="btn btn-outline-primary toggle-password" data-target="#password_confirmation" aria-label="Show password"><i data-lucide="eye"></i></button>
                    </div>
                </div>
                <div class="mb-3">
                    @php($siteKey = config('captcha.sitekey'))
                    @if(empty($siteKey))
                        <div class="alert alert-warning small mb-2">
                            reCAPTCHA is not configured. Please set NOCAPTCHA_SITEKEY and NOCAPTCHA_SECRET in your .env.
                        </div>
                    @else
                            <div id="recaptcha-container">
                                <div id="recaptcha-manual" style="min-height:78px; width: 304px;"></div>
                            </div>
                    @endif
                    @error('g-recaptcha-response')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            <div class="auth-actions d-grid mb-2">
                <button type="submit" class="btn btn-primary w-100" id="register-submit">
                    <i data-lucide="user-plus" class="me-1"></i>Create Account
                </button>
            </div>
        </form>

        <div class="auth-divider"><span>Or continue with</span></div>
        <div class="d-grid mb-2">
            <a href="{{ route('auth.google.redirect') }}" class="btn btn-outline-primary social-btn">
                <i data-lucide="mail"></i>
                Google
            </a>
        </div>

        <div class="auth-footer mt-3">Already have an account? <a href="{{ route('login') }}">Login</a></div>
    </div>
@endsection

@push('styles')
<style>
/* Make reCAPTCHA box visually consistent and all-white */
#recaptcha-container,
#recaptcha-manual {
    border: 0 !important;
    outline: 0 !important;
    box-shadow: none !important;
    background: #fff !important; /* all white */
    border-radius: 6px;
}
#recaptcha-manual > div,
#recaptcha-manual iframe {
    border: 0 !important;
    outline: 0 !important;
    box-shadow: none !important;
    background: transparent !important; /* let parent white show through */
}
</style>
@endpush

@push('scripts')
<script>
window.initRecaptcha = function(){
    try {
        var el = document.getElementById('recaptcha-manual');
        if (el && window.grecaptcha && typeof window.grecaptcha.render === 'function') {
            if (!el.hasChildNodes()) {
                window.grecaptcha.render('recaptcha-manual', { sitekey: @json(config('captcha.sitekey')), theme: 'light' });
            } else {
                // already rendered
            }
        }
    } catch(e) { /* no-op */ }
};
</script>
<script src="https://www.google.com/recaptcha/api.js?onload=initRecaptcha&render=explicit" async defer></script>
<script>
(function(){
    const $ = (sel) => document.querySelector(sel);
    const pwd = $('#password');
    const pwd2 = $('#password_confirmation');
    const nameInput = $('#name');
    const emailInput = $('#email');
    const submitBtn = $('#register-submit');


    const ruleEls = {
        length: $('#pw-rule-length'),
        mixed: $('#pw-rule-mixedcase'),
        number: $('#pw-rule-number'),
        symbol: $('#pw-rule-symbol'),
        nospace: $('#pw-rule-nospace'),
        personal: $('#pw-rule-personal'),
        seq: $('#pw-rule-seq'),
        repeat: $('#pw-rule-repeat'),
    };

    function mark(el, ok){
        if(!el) return;
        el.classList.toggle('text-success', !!ok);
        el.classList.toggle('text-danger', !ok);
    }

    function hasSequence(v){
        if(!v) return false;
        v = String(v).toLowerCase();
        const sequences = [
            'abcdefghijklmnopqrstuvwxyz',
            'qwertyuiopasdfghjklzxcvbnm',
            '0123456789'
        ];
        // Check common sequences
        for(const seq of sequences){
            for(let i=0;i<=seq.length-5;i++){
                if(v.includes(seq.slice(i, i+5))) return true;
            }
        }
        // Check ascending sequences within the value itself
        for(let i=0;i<=v.length-5;i++){
            let asc = true;
            for(let j=1;j<5;j++){
                if(v.charCodeAt(i+j) !== v.charCodeAt(i+j-1)+1){ asc = false; break; }
            }
            if(asc) return true;
        }
        return false;
    }

    function containsPersonal(v){
        v = (v || '').toLowerCase();
        const nameVal = (nameInput?.value || '').toLowerCase();
        const emailVal = (emailInput?.value || '').toLowerCase();
        const tokens = [];
        if(nameVal){ tokens.push(...nameVal.split(/[\s\-_.]+/).filter(t => t.length >= 3)); }
        if(emailVal){ tokens.push(...emailVal.split(/[@._\-+]/).filter(t => t.length >= 3)); }
        return tokens.some(t => v.includes(t));
    }

    function update(){
        const v = (pwd?.value || '');
        const lower = /[a-z]/.test(v);
        const upper = /[A-Z]/.test(v);
        const number = /\d/.test(v);
        const symbol = /[^A-Za-z0-9]/.test(v);
        const nospace = !/\s/.test(v);
        const norepeat = !(/(.)\1{3,}/.test(v));
        const noseq = !hasSequence(v);
        const nopersonal = !containsPersonal(v);

        mark(ruleEls.length, v.length >= 12);
        mark(ruleEls.mixed, lower && upper);
        mark(ruleEls.number, number);
        mark(ruleEls.symbol, symbol);
        mark(ruleEls.nospace, nospace);
        mark(ruleEls.repeat, norepeat);
        mark(ruleEls.seq, noseq);
        mark(ruleEls.personal, nopersonal);

        const minimumOk = v.length>=12 && lower && upper && number && symbol && nospace && norepeat && noseq && nopersonal;
        if(submitBtn){ submitBtn.disabled = !minimumOk; }
    }

    ['input','change','keyup'].forEach(ev => {
        pwd?.addEventListener(ev, update);
        pwd2?.addEventListener(ev, update);
        nameInput?.addEventListener(ev, update);
        emailInput?.addEventListener(ev, update);
    });
    update();

    // Try manual render in case auto-render didn't initialize the widget
    (function recaptchaManualRender(){
        var tries = 0, max = 12;
    var sitekey = @json(config('captcha.sitekey'));
        function tick(){
            tries++;
            var el = document.getElementById('recaptcha-manual');
            if (!el) return; // container missing
            if (window.grecaptcha && typeof window.grecaptcha.render === 'function') {
                if (!el.hasChildNodes()) {
                    try { window.grecaptcha.render('recaptcha-manual', { sitekey: sitekey, theme: 'light' }); } catch(e) {}
                }
                return; // success or already rendered
            }
            if (tries < max) setTimeout(tick, 500);
            else { /* give up silently */ }
        }
        setTimeout(tick, 500);
    })();
    // no UI debug hints in production for cleaner UX
})();
</script>
@endpush
