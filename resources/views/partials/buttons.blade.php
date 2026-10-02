<div class="flex flex-wrap gap-3 {{ $position === 'hero' ? 'mt-6' : '' }}">
    @if($siteData['bookingUrl'] ?? null)
    <a href="{{ $siteData['bookingUrl'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 bg-slate-900 text-white text-sm font-medium rounded-xl px-5 py-2.5 hover:bg-slate-800 transition-colors" data-umami-event="Schedule a call" data-umami-event-position="{{ $position }}">
        <x-icon name="lucide:calendar" class="size-4" />{{ $cta['bookingLabel'] }}<span class="sr-only">({{ $cta['newTabLabel'] }})</span>
    </a>
    @endif
    <a href="{{ $siteData['emailHref'] }}" class="inline-flex items-center gap-2 border border-slate-200 text-slate-700 text-sm font-medium rounded-xl px-5 py-2.5 hover:bg-slate-50 hover:border-slate-300 transition-colors" data-umami-event="Send an email" data-umami-event-position="{{ $position }}">
        <x-icon name="lucide:mail" class="size-4" />{{ $cta['emailLabel'] }}
    </a>
</div>
