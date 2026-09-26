<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Fatori Tech Solutions'))</title>
    <meta name="description" content="Instant Airtime, Data, and Bill Payments.">
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- 🚨 APPLY THEME BEFORE CSS LOADS (NO FOUC) --}}
    <script>
        (function () {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.classList.remove('light', 'dark');
            document.documentElement.classList.add(theme);
        })();
    </script>

    {{-- Styles --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        html { scroll-behavior: smooth; }
        [x-cloak] { display: none !important; }
    </style>
</head>


<body class="font-sans antialiased bg-background text-foreground transition-colors duration-300">

<div class="min-h-screen flex flex-col">
    {{-- Top Navigation --}}
    @include('layouts.navigation')

    {{-- Global Flash Message (Toast) --}}
    @if (session('status') || session('success') || session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="fixed top-5 right-5 z-50 transition-all duration-300"  style="display: none;">
            <div class="{{ session('error') ? 'bg-red-600' : 'bg-green-600' }} text-white px-6 py-3 rounded-lg shadow-lg flex items-center shadow-emerald-500/20">
                <span>{{ session('status') ?? session('success') ?? session('error') }}</span>
                <button @click="show = false" class="ml-4 text-white hover:text-gray-200">&times;</button>
            </div>
        </div>
    @endif

    {{-- Page Content --}}
    <main class="flex-1 py-6 px-4 sm:px-6 lg:px-8">
        {{-- ✅ FIX: Safely render slot (for Dashboard) OR yield content (for Admin pages) --}}
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">
        &copy; {{ date('Y') }} Fatori Tech Solutions. Backend Engineering Portfolio.
    </footer>
</div>

{{-- UPGRADED THEME TOGGLE SCRIPT --}}
<script>
    async function toggleTheme() {
        const root = document.documentElement;
        const isDark = root.classList.contains('dark');
        const newTheme = isDark ? 'light' : 'dark';

        root.classList.remove('light', 'dark');
        root.classList.add(newTheme);
        localStorage.setItem('theme', newTheme);

        const icon = document.getElementById('themeIcon');
        if (icon) icon.textContent = newTheme === 'dark' ? '🌞' : '🌙';

        // Optional backend sync (non-blocking)
        try {
            await fetch('/theme/toggle', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ theme: newTheme }),
            });
        } catch {
            // Silent fail — UI already updated
        }
    }
</script>

</body>
</html>
