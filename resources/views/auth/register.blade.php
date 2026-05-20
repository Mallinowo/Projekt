@extends('layouts.auth')
@section('content')
<div class="auth-card p-5 sm:p-7">
    <div class="text-center mb-6">
        <div class="auth-brand font-cinzel text-3xl font-bold text-purple-400 tracking-widest mb-2">ALTERMATCH</div>
        <p class="text-[#8f87b1] font-mono text-xs tracking-[.22em] uppercase">{{ __('app.tagline') }}</p>
    </div>

    <div class="auth-tabs mb-6">
        <a href="{{ route('login') }}" class="auth-tab">{{ __('auth.login') }}</a>
        <a href="{{ route('register') }}" class="auth-tab is-active">{{ __('auth.register') }}</a>
    </div>

    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf
        <div class="auth-grid-name-age">
            <div class="auth-field">
                <label class="field-label">{{ __('auth.username') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="30" class="field-input auth-input" placeholder="moonchild_x">
                @error('name')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="auth-field">
                <label class="field-label">{{ __('auth.age') }}</label>
                <input type="number" name="age" value="{{ old('age') }}" min="18" max="99" required class="field-input auth-input" placeholder="18">
                @error('age')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="auth-field">
            <label class="field-label">{{ __('auth.email') }}</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="field-input auth-input" placeholder="twoj@email.com">
            @error('email')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div class="auth-grid-two">
            <div class="auth-field">
                <label class="field-label">{{ __('auth.password') }}</label>
                <input type="password" name="password" required minlength="6" class="field-input auth-input" placeholder="min. 6 znakow">
                @error('password')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="auth-field">
                <label class="field-label">{{ __('auth.password_confirm') }}</label>
                <input type="password" name="password_confirmation" required class="field-input auth-input" placeholder="powtorz haslo">
            </div>
        </div>

        <div class="auth-grid-two">
            <div class="auth-field">
                <label class="field-label">{{ __('auth.city') }}</label>
                <input type="text" name="city" value="{{ old('city') }}" required class="field-input auth-input" placeholder="Warszawa">
                @error('city')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="auth-field">
                <label class="field-label">{{ __('auth.subculture') }}</label>
                <select name="subculture" required class="field-input auth-input">
                    <option value="">-- {{ __('auth.choose') }} --</option>
                    @foreach(['emo'=>'Emo','scene'=>'Scene','goth'=>'Goth','punk'=>'Punk','metalhead'=>'Metalhead'] as $val=>$label)
                        <option value="{{ $val }}" {{ old('subculture')===$val?'selected':'' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('subculture')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="auth-grid-two">
            <div class="auth-field">
                <label class="field-label">{{ __('auth.gender') }}</label>
                <select name="gender" required class="field-input auth-input">
                    <option value="">-- {{ __('auth.choose') }} --</option>
                    <option value="female" {{ old('gender')==='female' ? 'selected' : '' }}>{{ __('auth.gender_female') }}</option>
                    <option value="male" {{ old('gender')==='male' ? 'selected' : '' }}>{{ __('auth.gender_male') }}</option>
                    <option value="nonbinary" {{ old('gender')==='nonbinary' ? 'selected' : '' }}>{{ __('auth.gender_nonbinary') }}</option>
                    <option value="other" {{ old('gender')==='other' ? 'selected' : '' }}>{{ __('auth.gender_other') }}</option>
                </select>
                @error('gender')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="auth-field">
                <label class="field-label">{{ __('auth.orientation') }}</label>
                <select name="orientation" required class="field-input auth-input">
                    <option value="">-- {{ __('auth.choose') }} --</option>
                    <option value="hetero" {{ old('orientation')==='hetero' ? 'selected' : '' }}>{{ __('auth.orientation_hetero') }}</option>
                    <option value="homo" {{ old('orientation')==='homo' ? 'selected' : '' }}>{{ __('auth.orientation_homo') }}</option>
                    <option value="bi" {{ old('orientation')==='bi' ? 'selected' : '' }}>{{ __('auth.orientation_bi') }}</option>
                    <option value="other" {{ old('orientation')==='other' ? 'selected' : '' }}>{{ __('auth.orientation_other') }}</option>
                </select>
                @error('orientation')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>

        <button type="submit" class="auth-submit">
            {{ __('auth.register') }}
        </button>
    </form>
</div>
@endsection
