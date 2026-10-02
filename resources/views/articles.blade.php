@extends('layout')
@section('content')
<header class="relative mb-12">
    <h1 class="text-2xl sm:text-3xl font-medium text-slate-900 tracking-tight leading-tight mb-3">{{ $pageData['heading'] }}</h1>
    @if($pageData['intro'])<p class="text-sm text-slate-600">{{ $pageData['intro'] }}</p>@endif
</header>
<div class="flex flex-col divide-y divide-slate-100">@foreach($articles as $article)
    <a href="{{ $article->url() }}/" class="group py-4 first:pt-0 last:pb-0 block">
        <div class="flex items-baseline justify-between gap-4 mb-1"><h2 class="text-slate-900 font-medium text-sm group-hover:underline decoration-slate-300 underline-offset-4">{{ $article->get('title') }}</h2>@if($article->date())<time class="text-xs text-slate-500 shrink-0">{{ $article->date()->format('j M Y') }}</time>@endif</div>
        <p class="text-sm text-slate-600 leading-relaxed">{{ $article->get('excerpt') }}</p>
    </a>
@endforeach</div>
@if($totalPages > 1)
<nav aria-label="Pagination" class="mt-12 pt-8 border-t border-slate-100 flex items-center justify-between text-sm">
    <div class="w-24">@if($pageNumber > 1)<a href="{{ $pageNumber === 2 ? '/articles/' : '/articles/'.($pageNumber - 1).'/' }}" class="text-slate-500 hover:text-slate-700 transition-colors">&larr; {{ $labels['previous'] }}</a>@endif</div>
    <span class="text-slate-500 text-xs">{{ $labels['pageLabel'] }} {{ $pageNumber }} {{ $labels['ofLabel'] }} {{ $totalPages }}</span>
    <div class="w-24 text-right">@if($pageNumber < $totalPages)<a href="/articles/{{ $pageNumber + 1 }}/" class="text-slate-500 hover:text-slate-700 transition-colors">{{ $labels['next'] }} &rarr;</a>@endif</div>
</nav>
@endif
@endsection
