<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="@yield('meta-description', '')" />
    <title>@yield('title', 'МНОГОРЕСУРСОВ')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body @yield('body-attrs')>
@yield('content')
@stack('scripts')
</body>
</html>
