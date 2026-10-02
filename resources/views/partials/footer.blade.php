<footer class="mt-16 pt-8 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-slate-500">
    <div>&copy; {{ date('Y') }} {{ $siteData['name'] }}.</div>
    <div class="flex gap-6">
        <span class="flex items-center gap-1.5"><x-icon name="solar:map-point-linear" class="size-3.5" />{{ $siteData['location'] }}</span>
        <span class="flex items-center gap-1.5" title="{{ $siteData['timezone'] }}">
            <x-icon name="solar:clock-circle-linear" class="size-3.5" />
            <time id="local-time" data-tz="{{ $siteData['timezone'] }}" x-data="localClock" x-text="time">{{ $siteData['timezone'] }}</time>
        </span>
    </div>
</footer>
