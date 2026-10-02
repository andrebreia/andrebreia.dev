@extends('layout')
@section('content')
<header class="relative mb-12">
    <h1 class="text-2xl sm:text-3xl font-medium text-slate-900 tracking-tight leading-tight mb-3">{{ $pageData['heading'] }}</h1>
    @if($pageData['intro'])<p class="text-sm text-slate-600">{{ $pageData['intro'] }}</p>@endif
</header>
@foreach($pageData['sections'] as $section)
<section class="mb-14">
    <x-section-heading :icon="$section['icon']" :label="$section['label']" />
    <div class="flex flex-col divide-y divide-slate-100">
    @foreach($section['items'] as $item)
        <div class="group py-3">
            <h3 class="text-slate-900 font-medium text-sm">
                @if($item['link'] ?? null)<a href="{{ $item['link'] }}" target="_blank" rel="noopener noreferrer" class="hover:underline decoration-slate-300 underline-offset-4">{{ $item['title'] }} <span class="sr-only">({{ $cta['newTabLabel'] }})</span></a>@else{{ $item['title'] }}@endif
            </h3>
            <p class="uses-text text-slate-600 text-sm mt-0.5 leading-relaxed">{!! \Illuminate\Support\Str::inlineMarkdown($item['text'], ['html_input' => 'strip', 'allow_unsafe_links' => false, 'default_attributes' => ['a' => ['target' => '_blank', 'rel' => 'noopener noreferrer']]]) !!}</p>
        </div>
    @endforeach
    </div>
</section>
@endforeach
@endsection
