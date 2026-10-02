@extends('layout')
@section('content')
<div class="flex flex-col items-center justify-center py-16 text-center">
    <span class="text-6xl font-medium text-slate-200 font-heading mb-4">404</span>
    <h1 class="text-xl font-medium text-slate-900 mb-2 font-heading">{{ $pageData['heading'] }}</h1>
    <p class="text-sm text-slate-500 mb-8 max-w-sm">{{ $pageData['intro'] }}</p>
    <a href="/" class="inline-flex items-center gap-2 bg-slate-900 text-white text-sm font-medium rounded-xl px-5 py-2.5 hover:bg-slate-800 transition-colors"><x-icon name="lucide:arrow-left" class="size-4" />{{ $labels['backToHome'] }}</a>
</div>
@endsection
