@extends('layout')
@section('content')
<header class="relative mb-12">
    <h1 class="text-2xl sm:text-3xl font-medium text-slate-900 tracking-tight leading-tight mb-3">{{ $pageData['heading'] }}</h1>
    @if($pageData['intro'])<p class="text-sm text-slate-600 leading-relaxed max-w-lg">{{ $pageData['intro'] }}</p>@endif
</header>
<section class="mb-14">
    <h2 class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-6">{{ $pageData['services_label'] }}</h2>
    <div class="flex flex-col gap-1">@foreach($services as $service)<x-service-item :service="$service" :expanded="true" />@endforeach</div>
</section>
@if($pageData['projects'])
<section class="mb-14">
    <h2 class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-6">{{ $pageData['projects_label'] }}</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">@foreach($pageData['projects'] as $project)
        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-4"><h3 class="text-slate-900 font-medium text-sm mb-2">{{ $project['title'] }}</h3><p class="text-sm text-slate-600 leading-relaxed">{{ $project['description'] }}</p></div>
    @endforeach</div>
</section>
@endif
@if($pageData['faqs'])
<section class="mb-14">
    <h2 class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-6">{{ $pageData['faqs_label'] }}</h2>
    <div class="space-y-6">@foreach($pageData['faqs'] as $faq)
        <div><h3 class="text-slate-900 font-medium text-sm mb-2">{{ $faq['question'] }}</h3><p class="text-sm text-slate-600 leading-relaxed">{{ $faq['answer'] }}</p></div>
    @endforeach</div>
</section>
@endif
@include('partials.cta')
@endsection
