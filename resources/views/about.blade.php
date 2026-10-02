@extends('layout')
@section('content')
<header class="relative mb-12">
    <img src="{{ Statamic::tag('glide')->src('images::'.$siteData['portrait'])->width(80)->height(80)->format('webp')->quality(80)->fetch() }}" alt="{{ $siteData['portraitAlt'] }}" width="80" height="80" class="w-20 h-20 rounded-2xl object-cover mb-6" fetchpriority="high" decoding="async">
    <h1 class="text-2xl sm:text-3xl font-medium text-slate-900 tracking-tight leading-tight mb-3">{{ $pageData['heading'] }}</h1>
</header>
<article class="prose prose-slate prose-sm max-w-none prose-headings:font-heading prose-headings:tracking-tight prose-h2:text-lg prose-h2:mt-10 prose-h2:mb-4 prose-p:text-slate-600 prose-p:leading-relaxed prose-strong:text-slate-700 prose-strong:font-medium prose-a:text-slate-700 prose-a:underline prose-a:decoration-slate-300 prose-a:underline-offset-4 hover:prose-a:decoration-slate-500">{!! $bodyHtml !!}</article>
@if($certifications)
<section class="mt-14 mb-14">
    <x-section-heading icon="solar:medal-ribbon-star-linear" :label="$pageData['certifications_label']" />
    @include('partials.certifications')
</section>
@endif
@include('partials.cta')
@endsection
