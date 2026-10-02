@props(['name'])
@php
    $icon = app(\App\Site\Icons::class)->get($name);
@endphp
@if($icon)
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {{ $icon['width'] }} {{ $icon['height'] }}" {{ $attributes->merge(['aria-hidden' => 'true']) }}>{!! $icon['body'] !!}</svg>
@endif
