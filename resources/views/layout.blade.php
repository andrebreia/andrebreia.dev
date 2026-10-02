<!doctype html>
<html lang="en" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seo['title'] }}</title>
    <meta name="description" content="{{ $seo['description'] }}">
    @if($seo['robots'])<meta name="robots" content="{{ $seo['robots'] }}">@endif
    <link rel="canonical" href="{{ $seo['canonical'] }}">
    <link rel="preload" href="/fonts/GeistVF.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png" sizes="180x180">
    <link rel="manifest" href="/site.webmanifest">
    <meta property="og:title" content="{{ $seo['title'] }}">
    <meta property="og:description" content="{{ $seo['description'] }}">
    <meta property="og:url" content="{{ $seo['canonical'] }}">
    <meta property="og:type" content="{{ $seo['type'] }}">
    <meta property="og:image" content="{{ $seo['image'] }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo['title'] }}">
    <meta name="twitter:description" content="{{ $seo['description'] }}">
    <meta name="twitter:image" content="{{ $seo['image'] }}">
    <script type="application/ld+json">{!! json_encode($seo['jsonLd'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @production
        <script defer src="https://umami.breia.dev/script.js" data-website-id="c559bc93-558d-495c-9e18-ec6ad79d9337"></script>
    @endproduction
</head>
<body class="min-h-screen flex items-center justify-center sm:p-6 lg:p-8 text-slate-600 bg-slate-100 p-4">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:bg-slate-900 focus:text-white focus:px-4 focus:py-2 focus:rounded-lg focus:text-sm">{{ $labels['skipToContent'] }}</a>
    <main id="main" class="w-full max-w-3xl bg-white rounded-[2rem] shadow-lg border border-slate-200/60 p-8 sm:p-12 md:p-16 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-b from-slate-50/50 to-transparent pointer-events-none" aria-hidden="true"></div>
        @yield('content')
        @include('partials.footer')
    </main>
    @include('partials.navigation')
</body>
</html>
