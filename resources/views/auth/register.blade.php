@extends('layouts.auth')
@section('content')
<div class="bg-[#111118] border border-[#2a2a3a] rounded-2xl p-8 shadow-2xl">
    <div class="font-cinzel text-2xl font-bold text-purple-400 text-center tracking-widest mb-1" style="text-shadow:0 0 30px rgba(168,85,247,.5)">✦ ALTERMATCH</div>
    <p class="text-center text-[#5e5880] font-mono text-xs tracking-widest mb-6">{{ __('app.tagline') }}</p>

    <div class="flex border border-[#2a2a3a] rounded-lg overflow-hidden mb-6">
        <a href="{{ route('login') }}" class="flex-1 text-center py-2 font-mono text-xs uppercase tracking-widest text-[#5e5880] hover:text-purple-400 transition-colors">{{ __('auth.login') }}</a>
        <a href="{{ route('register') }}" class="flex-1 text-center py-2 font-mono text-xs uppercase tracking-widest bg-purple-600 text-white">{{ __('auth.register') }}</a>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
                <label class="field-label">{{ __('auth.username') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="30" class="field-input" placeholder="moonchild_x">
                @error('name')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="field-label">{{ __('auth.age') }}</label>
                <input type="number" name="age" value="{{ old('age') }}" min="18" max="99" required class="field-input" placeholder="18">
                @error('age')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="mb-3">
            <label class="field-label">{{ __('auth.email') }}</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="field-input" placeholder="twoj@email.com">
            @error('email')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
                <label class="field-label">{{ __('auth.password') }}</label>
                <input type="password" name="password" required minlength="6" class="field-input" placeholder="min. 6 znaków">
                @error('password')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="field-label">{{ __('auth.password_confirm') }}</label>
                <input type="password" name="password_confirmation" required class="field-input" placeholder="powtórz hasło">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <label class="field-label">{{ __('auth.city') }}</label>
                <input type="text" name="city" value="{{ old('city') }}" required class="field-input" placeholder="Warszawa">
                @error('city')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="field-label">{{ __('auth.subculture') }}</label>
                <select name="subculture" required class="field-input">
                    <option value="">— {{ __('auth.choose') }} —</option>
                    @foreach(['emo'=>'🖤 Emo','scene'=>'🌈 Scene','goth'=>'🦇 Goth','punk'=>'⚡ Punk','metalhead'=>'🤘 Metalhead'] as $val=>$label)
                        <option value="{{ $val }}" {{ old('subculture')===$val?'selected':'' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('subculture')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <label class="field-label">{{ __('auth.gender') }}</label>
                <select name="gender" required class="field-input">
                    <option value="">— {{ __('auth.choose') }} —</option>
                    <option value="female" {{ old('gender')==='female' ? 'selected' : '' }}>{{ __('auth.gender_female') }}</option>
                    <option value="male" {{ old('gender')==='male' ? 'selected' : '' }}>{{ __('auth.gender_male') }}</option>
                    <option value="nonbinary" {{ old('gender')==='nonbinary' ? 'selected' : '' }}>{{ __('auth.gender_nonbinary') }}</option>
                    <option value="other" {{ old('gender')==='other' ? 'selected' : '' }}>{{ __('auth.gender_other') }}</option>
                </select>
                @error('gender')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="field-label">{{ __('auth.orientation') }}</label>
                <select name="orientation" required class="field-input">
                    <option value="">— {{ __('auth.choose') }} —</option>
                    <option value="hetero" {{ old('orientation')==='hetero' ? 'selected' : '' }}>{{ __('auth.orientation_hetero') }}</option>
                    <option value="homo" {{ old('orientation')==='homo' ? 'selected' : '' }}>{{ __('auth.orientation_homo') }}</option>
                    <option value="bi" {{ old('orientation')==='bi' ? 'selected' : '' }}>{{ __('auth.orientation_bi') }}</option>
                    <option value="other" {{ old('orientation')==='other' ? 'selected' : '' }}>{{ __('auth.orientation_other') }}</option>
                </select>
                @error('orientation')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <button type="submit" class="w-full bg-gradient-to-br from-purple-500 to-violet-700 text-white py-3 rounded-lg font-mono text-xs uppercase tracking-widest shadow-[0_0_20px_rgba(168,85,247,.3)] hover:shadow-[0_0_35px_rgba(168,85,247,.55)] hover:-translate-y-0.5 transition-all">
            {{ __('auth.register') }}
        </button>
    </form>
</div>
@endsection
