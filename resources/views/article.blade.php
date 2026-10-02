@extends('layout')
@section('content')
<header class="relative mb-12">
    <a id="back-link" href="/articles/" x-data @click="if (document.referrer && new URL(document.referrer).origin === location.origin && new URL(document.referrer).pathname.startsWith('/articles')) { $event.preventDefault(); history.back() }" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-600 transition-colors mb-8"><x-icon name="lucide:arrow-left" class="size-3.5" />{{ $labels['backToArticles'] }}</a>
    <h1 class="text-2xl sm:text-3xl font-medium text-slate-900 tracking-tight leading-tight">{{ $pageData['title'] }}</h1>
</header>
@include('partials.body')
@endsection
