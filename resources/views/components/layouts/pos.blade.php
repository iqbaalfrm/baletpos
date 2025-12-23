<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>POS KASIR</title>
    
    <style>
        [x-cloak] { display: none !important; }
        /* Hide Scrollbar */
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    @filamentStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-950 text-white h-screen w-full overflow-hidden font-sans">
    
    <main class="h-full w-full">
        {{ $slot }}
    </main>

    @livewire('notifications')
    @filamentScripts
</body>
</html>