<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Service Waiver') — {{ config('app.name') }}</title>
        @vite(['resources/css/app.css'])
    </head>
    <body class="min-h-screen bg-background font-sans antialiased text-foreground">
        <main class="mx-auto max-w-lg px-4 py-12">
            @yield('content')
        </main>
    </body>
</html>
