@extends('layout')
@section('content')
<header class="relative mb-12">
    <h1 class="text-2xl sm:text-3xl font-medium text-slate-900 tracking-tight leading-tight mb-3">{{ $pageData['heading'] }}</h1>
    @if($pageData['intro'] ?? null)<p class="text-sm text-slate-600">{{ $pageData['intro'] }}</p>@endif
</header>
@include('partials.body')
@include('partials.cta')
@endsection
