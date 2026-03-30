<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@hasSection('title')@yield('title') | @endif{{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('img/ikonaa.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/ikonaa.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Pro:wght@300;400&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0a0a0f] text-[#e2e0f0] font-crimson h-full flex items-center justify-center" style="background-image:radial-gradient(ellipse at 50% 30%,rgba(168,85,247,.12),transparent 70%)">
    <div class="w-full max-w-sm px-4">
        {{-- Language switcher --}}
        <div class="flex justify-end gap-2 mb-4">
            <a href="{{ route('locale','pl') }}" class="lang-btn {{ app()->getLocale()==='pl'?'active':'' }}">PL</a>
            <a href="{{ route('locale','en') }}" class="lang-btn {{ app()->getLocale()==='en'?'active':'' }}">EN</a>
        </div>
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>
