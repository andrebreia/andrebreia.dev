<div class="flex flex-col gap-1">
    @foreach($certifications as $certification)
    <a href="{{ $certification['url'] }}" target="_blank" rel="noopener noreferrer" class="group flex items-center justify-between p-3 -mx-3 rounded-xl hover:bg-slate-50 transition-all duration-200">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 group-hover:text-slate-600 group-hover:bg-white group-hover:shadow-sm transition-all shrink-0">
                <x-icon :name="$certification['icon']" class="size-5" />
            </div>
            <div><h3 class="text-slate-900 font-medium text-sm">{{ $certification['title'] }}</h3><p class="text-slate-600 text-xs mt-0.5">{{ $certification['issuer'] }}</p></div>
        </div>
        <x-icon name="solar:arrow-right-up-linear" class="size-4 text-slate-400 group-hover:text-slate-600 transition-colors" />
    </a>
    @endforeach
</div>
