<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'SKYFORGE') }}</title>

        <!-- SEO -->
        <meta name="description" content="{{ \App\Models\Setting::get('seo_description', 'SKYFORGE — апгрейд скинов CS2') }}">
        <meta name="keywords" content="{{ \App\Models\Setting::get('seo_keywords', 'CS2, скины, апгрейд') }}">
        <meta name="author" content="SKYFORGE">
        <meta name="robots" content="index, follow">
        <meta name="theme-color" content="#070A10">

        <!-- Open Graph -->
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ config('app.name', 'SKYFORGE') }}">
        <meta property="og:title" content="{{ \App\Models\Setting::get('seo_title', 'SKYFORGE — Апгрейд скинов CS2') }}">
        <meta property="og:description" content="{{ \App\Models\Setting::get('seo_description', 'SKYFORGE — апгрейд скинов CS2') }}">
        <meta property="og:image" content="{{ asset('assets/img/og-image.png') }}">
        <meta property="og:url" content="{{ url('/') }}">
        <meta property="og:locale" content="ru_RU">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ \App\Models\Setting::get('seo_title', 'SKYFORGE') }}">
        <meta name="twitter:description" content="{{ \App\Models\Setting::get('seo_description', 'SKYFORGE — апгрейд скинов CS2') }}">
        <meta name="twitter:image" content="{{ asset('assets/img/og-image.png') }}">

        <!-- Favicon -->
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon-32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicon-16.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/apple-touch-icon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Antonio:wght@100..700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/Pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
