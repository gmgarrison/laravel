<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="flex min-h-screen flex-col bg-white antialiased dark:bg-zinc-800">
        @include('layouts.guest.header')

        <main class="flex flex-1 flex-col">
            {{ $slot }}
        </main>

        @include('layouts.guest.footer')

        @fluxScripts
    </body>
</html>
