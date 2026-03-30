<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') | @endif{{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('img/ikonaa.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/ikonaa.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Pro:wght@300;400&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        input.field-input,
        textarea.field-input,
        select.field-input {
            background-color: #16161f !important;
            color: #e2e0f0 !important;
        }
        input.field-input:-webkit-autofill,
        input.field-input:-webkit-autofill:hover,
        input.field-input:-webkit-autofill:focus,
        textarea.field-input:-webkit-autofill,
        textarea.field-input:-webkit-autofill:hover,
        textarea.field-input:-webkit-autofill:focus,
        select.field-input:-webkit-autofill,
        select.field-input:-webkit-autofill:hover,
        select.field-input:-webkit-autofill:focus {
            -webkit-text-fill-color: #e2e0f0 !important;
            caret-color: #e2e0f0 !important;
            -webkit-box-shadow: 0 0 0 1000px #16161f inset !important;
            box-shadow: 0 0 0 1000px #16161f inset !important;
            background-color: #16161f !important;
            border-color: #2a2a3a !important;
        }
    </style>
</head>
<body class="bg-[#0a0a0f] text-[#e2e0f0] font-crimson h-full overflow-hidden">

{{-- NAV --}}
<nav class="flex items-center justify-between px-4 md:px-6 h-16 bg-[#111118] border-b border-[#2a2a3a] z-50 relative flex-shrink-0">
    <a href="{{ route('discover') }}" class="font-cinzel text-lg font-bold tracking-widest text-purple-400 hover:text-[#d6b6ff] transition-colors" style="text-shadow:0 0 18px rgba(168,85,247,.5)">
        ✦ ALTERMATCH
    </a>
    <div class="flex gap-1 items-center">
        <a href="{{ route('discover') }}" class="nav-btn {{ request()->routeIs('discover','home') ? 'active' : '' }}">{{ __('nav.discover') }}</a>
        <a href="{{ route('chat') }}"     class="nav-btn {{ request()->routeIs('chat*') ? 'active' : '' }} relative">
            {{ __('nav.chat') }}
            @if(isset($unreadCount) && $unreadCount > 0)
                <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-pink-500 shadow-[0_0_6px_theme(colors.pink.500)]"></span>
            @endif
        </a>
        <a href="{{ route('profile') }}"  class="nav-btn {{ request()->routeIs('profile*') ? 'active' : '' }}">{{ __('nav.profile') }}</a>
        <div class="w-px h-6 bg-[#2a2a3a] mx-1"></div>

        {{-- Avatar --}}
        <a href="{{ route('profile') }}" class="w-10 h-10 rounded-full border-2 border-[#2a2a3a] overflow-hidden flex items-center justify-center bg-gradient-to-br from-purple-900 to-blue-900 hover:border-purple-400 transition-colors">
            @if(auth()->user()->getAvatarUrl())
                <img src="{{ auth()->user()->getAvatarUrl() }}" class="w-full h-full object-cover">
            @else
                <span class="text-xs font-bold">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span>
            @endif
        </a>

        {{-- Language switcher (punkt 12) --}}
        <div class="flex gap-1 ml-1">
            <a href="{{ route('locale','pl') }}" class="lang-btn {{ app()->getLocale()==='pl' ? 'active' : '' }}">PL</a>
            <a href="{{ route('locale','en') }}" class="lang-btn {{ app()->getLocale()==='en' ? 'active' : '' }}">EN</a>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="ml-1">
            @csrf
            <button class="font-mono text-[.66rem] text-[#5e5880] border border-[#2a2a3a] px-2.5 py-1 rounded hover:border-red-500 hover:text-red-500 transition-all uppercase tracking-wider">
                {{ __('auth.logout') }}
            </button>
        </form>
    </div>
</nav>

<div class="flex-1 overflow-hidden">
    @yield('content')
</div>

{{-- Toast --}}
<div id="toast" class="fixed bottom-6 left-1/2 -translate-x-1/2 translate-y-20 bg-[#111118] border border-[#2a2a3a] text-[#e2e0f0] px-5 py-2 rounded-full font-mono text-xs tracking-wide transition-transform duration-300 z-[700] whitespace-nowrap shadow-2xl opacity-0" id="toast"></div>

@stack('scripts')
</body>
</html>
