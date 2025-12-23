<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'POS System' }}</title>

    @filamentStyles
    @vite('resources/css/app.css')

    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>

<body x-data="{
    theme: localStorage.getItem('theme') || 'light',

    toggleTheme() {
        // Balik status
        this.theme = this.theme === 'light' ? 'dark' : 'light';

        // Simpan ke memori
        localStorage.setItem('theme', this.theme);

        // Update tampilan HTML
        if (this.theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
}" class="antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100">

    {{ $slot }}

    @filamentScripts
    @vite('resources/js/app.js')
</body>
</html>