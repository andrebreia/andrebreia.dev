@extends('layout')
@section('content')
<header class="relative mb-12">
    <a id="back-link" href="{{ $articlesUrl }}" x-data @click="if (document.referrer && new URL(document.referrer).origin === location.origin && (new URL(document.referrer).pathname.replace(/\/$/, '') === $el.pathname.replace(/\/$/, '') || new URL(document.referrer).pathname.startsWith($el.pathname))) { $event.preventDefault(); history.back() }" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-600 transition-colors mb-8"><x-icon name="lucide:arrow-left" class="size-3.5" />{{ $labels['backToArticles'] }}</a>
    <h1 class="text-2xl sm:text-3xl font-medium text-slate-900 tracking-tight leading-tight">{{ $pageData['title'] }}</h1>
    <div class="mt-4 flex flex-wrap items-baseline gap-x-3 gap-y-1 text-xs text-slate-500">
        <a rel="author" href="{{ $seo['authorUrl'] }}" class="underline decoration-slate-300 underline-offset-4 hover:text-slate-700">{{ $siteData['name'] }}</a>
        @if($seo['publishedAt'])<time datetime="{{ $seo['publishedAt'] }}">{{ $entry->date()->locale($language)->translatedFormat('j F Y') }}</time>@endif
        @if($seo['modifiedAt'] && $seo['modifiedAt'] !== $seo['publishedAt'])
            <span>{{ $labels['updated'] }} <time datetime="{{ $seo['modifiedAt'] }}">{{ \Carbon\Carbon::parse($seo['modifiedAt'])->locale($language)->translatedFormat('j F Y') }}</time></span>
        @endif
    </div>
</header>
@include('partials.body')
@endsection
