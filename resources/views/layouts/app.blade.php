<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Laviade')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    @stack('styles')
</head>
<body class="bg-gray-100">
    @include('layouts.navbar')
    
    <main>
        @yield('content')
    </main>
    
    @include('layouts.footer')
    
    @stack('scripts')
</body>
</html>
