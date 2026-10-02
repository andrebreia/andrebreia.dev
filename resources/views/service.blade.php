@extends('layout')
@section('content')
<header class="relative mb-12">
    <div class="flex items-center gap-4 mb-4">
        <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 shrink-0"><x-icon :name="$pageData['icon']" class="size-5" /></div>
        <h1 class="text-2xl sm:text-3xl font-medium text-slate-900 tracking-tight leading-tight">{{ $pageData['headline'] ?? $pageData['title'] }}</h1>
    </div>
</header>
@include('partials.body')
@include('partials.cta')
@endsection
