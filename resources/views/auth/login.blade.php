@extends('layouts.auth')
@section('content')
<div class="auth-card p-6 sm:p-8">
    <div class="text-center mb-7">
        <div class="auth-brand font-cinzel text-3xl font-bold text-purple-400 tracking-widest mb-2">ALTERMATCH</div>
        <p class="text-[#8f87b1] font-mono text-xs tracking-[.22em] uppercase">{{ __('app.tagline') }}</p>
    </div>

    <div class="auth-tabs mb-7">
        <a href="{{ route('login') }}" class="auth-tab is-active">{{ __('auth.login') }}</a>
        <a href="{{ route('register') }}" class="auth-tab">{{ __('auth.register') }}</a>
    </div>

    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf
        <div class="auth-field">
            <label class="field-label">{{ __('auth.email') }}</label>
            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                class="field-input auth-input"
                placeholder="twoj@email.com"
            >
            @error('email')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div class="auth-field">
            <label class="field-label">{{ __('auth.password') }}</label>
            <input
                type="password"
                name="password"
                required
                class="field-input auth-input"
                placeholder="********"
            >
        </div>

        <div class="flex items-center gap-2 rounded-lg border border-[#2a2a3a] bg-[#0d0d14]/70 px-3 py-2.5">
            <input type="checkbox" name="remember" id="remember" class="accent-purple-500 w-4 h-4">
            <label for="remember" class="text-xs text-[#9991bb]">{{ __('auth.remember') }}</label>
        </div>

        <button type="submit" class="auth-submit">
            {{ __('auth.login') }}
        </button>
    </form>

    <p class="auth-footer">
        {{ __('auth.no_account') }}
        <a href="{{ route('register') }}">{{ __('auth.register') }}</a>
    </p>
</div>
@endsection
