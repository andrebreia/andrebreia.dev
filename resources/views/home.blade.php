@extends('layout')
@section('content')
<header class="relative mb-16">
    <div class="flex justify-between items-start mb-8">
        <img src="{{ Statamic::tag('glide')->src('images::'.$siteData['logo'])->width(40)->height(40)->format('webp')->quality(80)->fetch() }}" srcset="{{ Statamic::tag('glide')->src('images::'.$siteData['logo'])->width(80)->height(80)->format('webp')->quality(80)->fetch() }} 2x" alt="{{ $siteData['logoAlt'] }}" width="40" height="40" class="h-10 w-10 rounded-lg shadow-sm" loading="eager" fetchpriority="high" decoding="async">
        <div role="status" class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-50 border border-slate-200/50">
            <span class="relative flex h-2 w-2" aria-hidden="true"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-cyan-500"></span></span>
            <span class="text-xs font-medium text-slate-500 tracking-tight">{{ $siteData['statusLabel'] }}</span>
        </div>
    </div>
    <h1 class="text-3xl sm:text-4xl font-medium text-slate-900 tracking-tight mb-4 leading-[1.1]">{{ $pageData['heading'] }}</h1>
    @if($pageData['bio'])<p class="text-base text-slate-600 leading-relaxed max-w-lg">{{ $pageData['bio'] }}</p>@endif
    @include('partials.buttons', ['position' => 'hero'])
    <nav aria-label="{{ $labels['socialLinks'] }}" class="flex gap-4 mt-6">
        @foreach($siteData['socialLinks'] as $link)
        <a href="{{ $link['href'] }}" aria-label="{{ $link['label'] }}" class="text-slate-500 hover:text-slate-800 transition-colors" @if(!str_starts_with($link['href'], 'mailto:')) target="_blank" rel="noopener noreferrer" @endif><x-icon :name="$link['icon']" class="size-5" /></a>
        @endforeach
    </nav>
</header>
<section class="mb-14">
    <x-section-heading icon="solar:widget-2-linear" :label="$pageData['services_label']" />
    <div class="flex flex-col gap-1">@foreach($services as $service)<x-service-item :service="$service" />@endforeach</div>
</section>
<section class="mb-14">
    <x-section-heading icon="solar:folder-with-files-linear" :label="$pageData['projects_label']" />
    <div class="flex flex-col gap-1">
        @foreach($projects as $project)
        <a href="{{ $project->url() }}/" class="group flex items-center justify-between p-3 -mx-3 rounded-xl hover:bg-slate-50 transition-all duration-200">
            <div class="flex items-center gap-4">
                @if($project->get('logo'))
                <div class="relative w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden group-hover:bg-white group-hover:shadow-sm transition-all shrink-0">
                    <img src="/images/{{ $project->get('logo') }}" alt="{{ $project->get('title') }} logo" class="w-full h-full object-contain p-1.5 relative z-0" loading="lazy" decoding="async">
                    <div class="absolute inset-0 bg-slate-500 mix-blend-color opacity-80 transition-opacity duration-200 group-hover:opacity-0 z-10"></div>
                </div>
                @else
                <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 group-hover:text-slate-600 group-hover:bg-white group-hover:shadow-sm transition-all shrink-0"><x-icon :name="$project->get('icon')" class="size-5" /></div>
                @endif
                <div><h3 class="text-slate-900 font-medium text-sm">{{ $project->get('title') }}</h3><p class="text-slate-500 text-xs mt-0.5">{{ $project->get('tags') }}</p></div>
            </div>
            <div class="flex items-center gap-3"><span class="text-xs text-slate-500 font-medium hidden sm:block">{{ $project->get('year') }}</span><x-icon name="solar:arrow-right-linear" class="size-4 text-slate-400 group-hover:text-slate-600 transition-colors" /></div>
        </a>
        @endforeach
    </div>
</section>
<section class="mb-14">
    <x-section-heading icon="solar:case-linear" :label="$pageData['experience_label']" />
    <div class="space-y-6 relative">
        <div class="absolute left-[5px] top-2 bottom-2 w-[1px] bg-slate-100"></div>
        @foreach($experience as $job)
        <div class="relative pl-6">
            <div class="absolute left-0 top-1.5 h-2.5 w-2.5 rounded-full border-2 border-white ring-1 ring-slate-100 {{ $job['isCurrent'] ? 'bg-slate-300' : 'bg-slate-200' }}" aria-hidden="true"></div>
            <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between mb-1"><h3 class="text-slate-900 font-medium text-sm">{{ $job['role'] }}@if($job['isCurrent']) <span class="sr-only">({{ $labels['current'] }})</span>@endif</h3><span class="text-xs text-slate-500">{{ $job['period'] }}</span></div>
            <div class="text-sm text-slate-600 mb-1">{{ $job['company'] }}</div>
            <p class="text-sm text-slate-600 leading-relaxed">{{ $job['description'] }}</p>
        </div>
        @endforeach
    </div>
</section>
@if($certifications)
<section class="mb-14">
    <x-section-heading icon="solar:medal-ribbon-star-linear" :label="$pageData['certifications_label']" />
    @include('partials.certifications')
</section>
@endif
@if($pageData['show_testimonials'] && $testimonials)
<section class="mb-14">
    <x-section-heading icon="solar:chat-square-like-linear" :label="$pageData['testimonials_label']" />
    <div class="space-y-4">@foreach($testimonials as $testimonial)
        <figure class="border-l-2 border-slate-200 pl-4 py-1"><blockquote><p class="text-sm text-slate-700 leading-relaxed mb-2">&ldquo;{{ $testimonial['quote'] }}&rdquo;</p></blockquote><figcaption class="text-xs text-slate-500"><span class="font-medium text-slate-700">{{ $testimonial['author'] }}</span>, {{ $testimonial['role'] }} at {{ $testimonial['company'] }}</figcaption></figure>
    @endforeach</div>
</section>
@endif
<section class="mb-14">
    <x-section-heading icon="solar:pen-new-square-linear" :label="$pageData['articles_label']" />
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">@foreach($latestArticles as $article)
        <a href="{{ $article->url() }}/" class="group block p-4 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 hover:border-slate-200 transition-all"><h3 class="text-slate-900 font-medium text-sm mb-2 group-hover:underline decoration-slate-300 underline-offset-4">{{ $article->get('title') }}</h3><p class="text-sm text-slate-600 leading-relaxed">{{ $article->get('excerpt') }}</p></a>
    @endforeach</div>
</section>
@include('partials.cta')
@endsection
