<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Serdadu') }} Admin</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')

        <style>
            body {
                font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                background-color: #e9f6f0;
                color: #0f172a;
            }

            .admin-shell {
                background: linear-gradient(180deg, #e7f5ee 0%, #dff1e7 50%, #d7eddf 100%);
                min-height: 100vh;
            }

            .admin-card {
                box-shadow: 0 18px 45px rgba(15, 118, 110, 0.08);
            }
        </style>
    </head>
    <body class="antialiased">
        <div class="admin-shell min-h-screen">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="relative">
                    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                        <div class="rounded-2xl bg-white border border-gray-200 shadow-sm p-6 flex flex-col gap-2">
                            {{ $header }}
                        </div>
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="pb-12">
                {{ $slot }}
            </main>
        </div>
        @stack('scripts')
    </body>
</html>
