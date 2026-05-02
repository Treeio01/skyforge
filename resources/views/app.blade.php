<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ \App\Models\Setting::get('seo_title') ?: config('app.name', 'GROWSKINS') }}</title>

        <!-- SEO -->
        <meta name="description" content="{{ \App\Models\Setting::get('seo_description', 'GROWSKINS — апгрейд скинов CS2') }}">
        <meta name="keywords" content="{{ \App\Models\Setting::get('seo_keywords', 'CS2, скины, апгрейд') }}">
        <meta name="author" content="GROWSKINS">
        <meta name="robots" content="index, follow">
        <meta name="theme-color" content="#070A10">

        <!-- Open Graph -->
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ config('app.name', 'GROWSKINS') }}">
        <meta property="og:title" content="{{ \App\Models\Setting::get('seo_title', 'GROWSKINS — Апгрейд скинов CS2') }}">
        <meta property="og:description" content="{{ \App\Models\Setting::get('seo_description', 'GROWSKINS — апгрейд скинов CS2') }}">
        <meta property="og:image" content="{{ asset('assets/img/og-image.png') }}">
        <meta property="og:url" content="{{ url('/') }}">
        <meta property="og:locale" content="ru_RU">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ \App\Models\Setting::get('seo_title', 'GROWSKINS') }}">
        <meta name="twitter:description" content="{{ \App\Models\Setting::get('seo_description', 'GROWSKINS — апгрейд скинов CS2') }}">
        <meta name="twitter:image" content="{{ asset('assets/img/og-image.png') }}">

        <!-- Favicon -->
        <link rel="icon" href="{{ \App\Models\Setting::get('favicon_url', asset('assets/img/favicon.ico')) }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon-32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicon-16.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/apple-touch-icon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Antonio:wght@100..700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

        <!-- Page transition styles -->
        <style>
            .page-loader {
                position: fixed;
                inset: 0;
                z-index: 99999;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #070A10;
                transition: opacity 0.4s ease, visibility 0.4s ease;
            }
            .page-loader.hidden {
                opacity: 0;
                visibility: hidden;
            }
            .loader-ring {
                width: 40px;
                height: 40px;
                border: 3px solid rgba(78, 137, 255, 0.15);
                border-top-color: #4E89FF;
                border-radius: 50%;
                animation: spin 0.8s linear infinite;
            }
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
        </style>

        <!-- Scripts -->
        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/Pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        <!-- Initial page loader -->
        <div id="page-loader" class="page-loader">
            <div class="loader-ring"></div>
        </div>

        @inertia
    </body>
</html>
