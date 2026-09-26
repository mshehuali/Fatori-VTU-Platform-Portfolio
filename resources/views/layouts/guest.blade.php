<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Fatori Tech') }}</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Scripts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* Premium Background Pattern */
        .bg-premium-dark {
            background-color: #0f111a;
            background-image: 
                radial-gradient(at 0% 0%, rgba(16, 185, 129, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.15) 0px, transparent 50%),
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 100% 100%, 100% 100%, 30px 30px, 30px 30px;
        }

        /* Autofill Fix */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px #161b26 inset !important;
            -webkit-text-fill-color: white !important;
            transition: background-color 5000s ease-in-out 0s;
        }
    </style>
</head>
<body class="font-sans text-gray-100 antialiased bg-premium-dark min-h-screen flex items-center justify-center p-4">
    
    {{-- Main Container --}}
    <div class="w-full max-w-md">
        {{-- Logo Area --}}
        <div class="flex justify-center mb-8">
            <a href="/" class="transition-transform hover:scale-105 duration-300">
                <x-application-logo class="w-16 h-16 fill-current text-emerald-500 drop-shadow-2xl" />
            </a>
        </div>

        {{-- Glass Card Slot --}}
        {{ $slot }}

        {{-- Footer --}}
        <div class="mt-8 text-center text-xs text-gray-600">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Secure & Encrypted.
        </div>
    </div>

</body>
</html>