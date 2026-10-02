@extends('layout')
@section('content')
<header class="relative mb-12">
    <div class="flex items-center gap-4 mb-4">
        @if($pageData['logo'] ?? null)
        <div class="relative w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden shrink-0">
            <img src="/images/{{ $pageData['logo'] }}" alt="{{ $pageData['title'] }} logo" class="w-full h-full object-contain p-1.5 relative z-0" loading="lazy" decoding="async"><div class="absolute inset-0 bg-slate-500 mix-blend-color opacity-80 z-10"></div>
        </div>
        @else
        <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 shrink-0"><x-icon :name="$pageData['icon']" class="size-5" /></div>
        @endif
        <div><h1 class="text-2xl sm:text-3xl font-medium text-slate-900 tracking-tight leading-tight">{{ $pageData['title'] }}</h1><p class="text-xs text-slate-500 mt-1">{{ $pageData['tags'] }} &middot; {{ $pageData['year'] }}</p></div>
    </div>
    <p class="text-base text-slate-600 leading-relaxed">{{ $pageData['excerpt'] }}</p>
    @if($pageData['external_url'] ?? null)
    <a href="{{ $pageData['external_url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 mt-4 text-sm text-slate-500 hover:text-slate-800 transition-colors"><x-icon name="solar:link-round-linear" class="size-4" />{{ preg_replace('/^www\./', '', parse_url($pageData['external_url'], PHP_URL_HOST) ?? $pageData['external_url']) }}<span class="sr-only">({{ $cta['newTabLabel'] }})</span></a>
    @endif
</header>
@include('partials.body')
@include('partials.cta')
@endsection
