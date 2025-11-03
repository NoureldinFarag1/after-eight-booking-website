@extends('layouts.app')
@section('title','Set New Password')
@section('body_class','auth-body')
@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-title">
            <i data-lucide="shield" class="text-primary" style="font-size:1.4rem;"></i>
            New Password
        </div>
        <div class="auth-subtitle">Choose a strong password for your account.</div>
        <form method="POST" action="{{ route('password.store') }}" class="auth-form" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email',$email) }}" class="form-control @error('email') is-invalid @enderror" required readonly>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3 position-relative">
                <label for="password" class="form-label">Password</label>
                <div class="input-group password-toggle-group">
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" required>
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
                    <div class="progress" role="progressbar" aria-label="Password strength" aria-valuemin="0" aria-valuemax="100">
                        <div id="pw-strength-bar" class="progress-bar bg-danger" style="width: 0%"></div>
                    </div>
                    <div id="pw-strength-text" class="small mt-1" aria-live="polite"></div>
                </div>
                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3 position-relative">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <div class="input-group password-toggle-group">
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="••••••••" required>
                    <button type="button" class="btn btn-outline-primary toggle-password" data-target="#password_confirmation" aria-label="Show password"><i data-lucide="eye"></i></button>
                </div>
            </div>
            <div class="auth-actions d-grid mb-2">
                <button class="btn btn-primary" id="reset-submit" type="submit"><i data-lucide="check-circle" class="me-1"></i>Reset Password</button>
            </div>
        </form>
        <div class="auth-footer"><a href="{{ route('login') }}">Login</a></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    const $ = (sel) => document.querySelector(sel);
    const pwd = $('#password');
    const pwd2 = $('#password_confirmation');
    const emailInput = $('#email');
    const submitBtn = $('#reset-submit');
    const bar = $('#pw-strength-bar');
    const txt = $('#pw-strength-text');

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
        const seqs = ['0123456789','abcdefghijklmnopqrstuvwxyz','qwertyuiop','asdfghjkl','zxcvbnm'];
        v = v.toLowerCase();
        for(const seq of seqs){
            for(let i=0;i<=seq.length-5;i++){
                const chunk = seq.substring(i,i+5);
                if(v.includes(chunk)) return true;
            }
        }
        return false;
    }

    function containsPersonal(v){
        const email = (emailInput?.value || '').toLowerCase();
        const local = email.split('@')[0] || '';
        if(local.length>=3 && v.includes(local)) return true;
        return false;
    }

    function score(v){
        let s = 0;
        const len = v.length;
        const lower = /[a-z]/.test(v);
        const upper = /[A-Z]/.test(v);
        const number = /\d/.test(v);
        const symbol = /[^A-Za-z0-9]/.test(v);
        const nospace = !/\s/.test(v);
        const norepeat = !(/(.)\1{3,}/.test(v));
        const noseq = !hasSequence(v);
        const nopersonal = !containsPersonal(v);

        if(len>=12) s+=2; if(len>=16) s+=1;
        if(lower) s+=1; if(upper) s+=1; if(number) s+=1; if(symbol) s+=1;
        if(nospace) s+=1; if(norepeat) s+=1; if(noseq) s+=1; if(nopersonal) s+=1;
        return Math.min(s, 12);
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

        const sc = score(v);
        let pct = Math.round((sc/12)*100);
        bar.style.width = pct + '%';
        bar.classList.remove('bg-danger','bg-warning','bg-success');
        let label = 'Weak';
        if(sc <= 4){ bar.classList.add('bg-danger'); label = 'Weak'; }
        else if(sc <= 8){ bar.classList.add('bg-warning'); label = 'Okay'; }
        else { bar.classList.add('bg-success'); label = 'Strong'; }
        txt.textContent = 'Password strength: ' + label;

        const minimumOk = v.length>=12 && lower && upper && number && symbol && nospace && norepeat && noseq && nopersonal;
        if(submitBtn){ submitBtn.disabled = !minimumOk; }
    }

    ['input','change','keyup'].forEach(ev => {
        pwd?.addEventListener(ev, update);
        pwd2?.addEventListener(ev, update);
        emailInput?.addEventListener(ev, update);
    });
    update();
})();
</script>
@endpush
