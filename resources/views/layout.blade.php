<!doctype html>
<html lang="{{ $language }}" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seo['title'] }}</title>
    <meta name="description" content="{{ $seo['description'] }}">
    @if($seo['robots'])<meta name="robots" content="{{ $seo['robots'] }}">@endif
    @if($seo['canonical'])<link rel="canonical" href="{{ $seo['canonical'] }}">@endif
    <link rel="ard" href="{{ route('agent.ard') }}" type="application/json">
    @foreach($seo['alternates'] as $locale => $url)
    <link rel="alternate" hreflang="{{ $locale }}" href="{{ $url }}">
    @endforeach
    <link rel="preload" href="{{ Vite::asset('resources/fonts/GeistVF-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png" sizes="180x180">
    <link rel="manifest" href="/site.webmanifest">
    <meta property="og:title" content="{{ $seo['title'] }}">
    <meta property="og:description" content="{{ $seo['description'] }}">
    @if($seo['canonical'])<meta property="og:url" content="{{ $seo['canonical'] }}">@endif
    <meta property="og:site_name" content="{{ $siteData['name'] }}">
    <meta property="og:type" content="{{ $seo['type'] }}">
    <meta property="og:locale" content="{{ $language === 'fr' ? 'fr_FR' : 'en_GB' }}">
    @if($seo['alternates'])<meta property="og:locale:alternate" content="{{ $language === 'fr' ? 'en_GB' : 'fr_FR' }}">@endif
    <meta property="og:image" content="{{ $seo['image'] }}">
    <meta property="og:image:alt" content="{{ $seo['imageAlt'] }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    @if($seo['publishedAt'])<meta property="article:published_time" content="{{ $seo['publishedAt'] }}">@endif
    @if($seo['modifiedAt'])<meta property="article:modified_time" content="{{ $seo['modifiedAt'] }}">@endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo['title'] }}">
    <meta name="twitter:description" content="{{ $seo['description'] }}">
    <meta name="twitter:image" content="{{ $seo['image'] }}">
    <meta name="twitter:image:alt" content="{{ $seo['imageAlt'] }}">
    @if($seo['jsonLd']['@graph'])<script type="application/ld+json">{!! json_encode($seo['jsonLd'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>@endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @production
        <script defer src="https://umami.breia.dev/script.js" data-website-id="c559bc93-558d-495c-9e18-ec6ad79d9337"></script>
    @endproduction
</head>
<body class="min-h-screen flex items-center justify-center sm:p-6 lg:p-8 text-slate-600 bg-slate-100 p-4">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:bg-slate-900 focus:text-white focus:px-4 focus:py-2 focus:rounded-lg focus:text-sm">{{ $labels['skipToContent'] }}</a>
    <main id="main" class="w-full max-w-3xl bg-white rounded-[2rem] shadow-lg border border-slate-200/60 p-8 sm:p-12 md:p-16 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-b from-slate-50/50 to-transparent pointer-events-none" aria-hidden="true"></div>
        <nav data-language-switcher aria-label="{{ $labels['languageNavigation'] }}" class="relative flex flex-wrap justify-end items-center gap-3 mb-8 text-xs">
            @foreach(['en' => 'English', 'fr' => 'Français'] as $locale => $name)
                @if($locale === $language)
                    <span lang="{{ $locale }}" aria-current="true" class="font-medium text-slate-900">{{ $name }}</span>
                @else
                    <a href="{{ $languageUrl }}" hreflang="{{ $locale }}" lang="{{ $locale }}" class="rounded-md px-2 py-1 text-slate-500 hover:bg-slate-100 hover:text-slate-900 transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900">{{ $hasTranslation ? $name : $labels[$locale === 'fr' ? 'frenchHome' : 'englishHome'] }}</a>
                @endif
            @endforeach
        </nav>
        @yield('content')
        @include('partials.footer')
    </main>
    @include('partials.navigation')
</body>
</html>
