<div id="floating-nav-wrapper" class="floating-nav-wrapper fixed left-0 right-0 z-40 flex justify-center" x-data="floatingNav" :class="{'is-hidden': hidden, 'is-bouncing': bouncing}" @scroll.window.passive="onScroll">
    <nav aria-label="{{ $labels['mainNavigation'] }}" class="flex items-center gap-1 bg-slate-900/90 backdrop-blur-sm rounded-full px-2 py-2 shadow-lg shadow-black/20 border border-white/10 ring-1 ring-inset ring-white/[0.07]" @mouseenter="reveal" @animationend.self="bouncing = false">
        @foreach($navigation as $link)
            @php $active = $link['href'] === '/' ? request()->path() === '/' : str_starts_with('/'.request()->path().'/', $link['href']); @endphp
            @if($link['hasChildren'] ?? false)
                <div class="services-stack relative" id="services-stack" x-data="servicesFan" :class="{'is-open': open}" @mouseenter="show" @mouseleave="hide" @focusin="show" @focusout="if (!$el.contains($event.relatedTarget)) hide()" @keydown.escape.stop.prevent="$el.querySelector('.services-trigger').focus(); open = false" @click.outside="open = false">
                    <a href="{{ $link['href'] }}" @if($active) aria-current="page" @endif aria-expanded="false" :aria-expanded="open" class="services-trigger px-3 py-1.5 rounded-full text-sm font-medium transition-colors {{ $active ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/5' }}">{{ $link['label'] }}</a>
                    <div class="stack-fan" aria-label="{{ $labels['servicesSubmenu'] }}">
                        @foreach($services as $service)
                        <a href="{{ $service->url() }}/" class="stack-item" :tabindex="open ? 0 : -1" tabindex="-1" style="--fan-rotate: {{ -$loop->index * 3 }}deg" :style="{transitionDelay: (open ? {{ $loop->index * 40 }} : {{ ($services->count() - 1 - $loop->index) * 30 }}) + 'ms'}">
                            <x-icon :name="$service->get('icon')" class="size-4" /><span>{{ $service->get('title') }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
            @else
                <a href="{{ $link['href'] }}" @if($active) aria-current="page" @endif @if($link['icon'] ?? false) aria-label="{{ $link['label'] }}" @endif class="{{ ($link['icon'] ?? false) ? 'flex items-center justify-center size-9' : 'px-3 py-1.5 text-sm font-medium' }} rounded-full transition-colors {{ $active ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/5' }}">
                    @if($link['icon'] ?? false)<x-icon :name="$link['icon']" class="size-5" />@else{{ $link['label'] }}@endif
                </a>
            @endif
        @endforeach
    </nav>
</div>
