<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '견적서')</title>
    @yield('styles')
    <style>
        body { margin: 0; padding: 20px; font-family: 'Malgun Gothic', sans-serif; }
        @media print { body { padding: 0; } }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    @yield('content')
    @stack('scripts')
</body>
</html>
