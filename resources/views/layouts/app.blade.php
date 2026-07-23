<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Taskate</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('scripts')
</head>
<body class="bg-gray-50 h-screen flex overflow-hidden">

    <x-sidebar />

    <div class="flex flex-col flex-1 overflow-hidden min-w-0">
        @if(!request()->routeIs('eisenhower*'))
        <x-topbar />
        @endif
        <main class="flex-1 overflow-y-auto p-6">
            {{ $slot }}
        </main>
    </div>

</body>
</html>
