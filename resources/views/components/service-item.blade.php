@props(['service', 'expanded' => false])
<a href="{{ $service->url() }}/" class="group flex items-start gap-4 p-3 -mx-3 rounded-xl hover:bg-slate-50 transition-colors">
    <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 shrink-0">
        <x-icon :name="$service->get('icon')" class="size-5" />
    </div>
    <div class="flex-1">
        <div class="flex items-center justify-between gap-2">
            <h3 class="text-slate-900 font-medium text-sm group-hover:underline decoration-slate-300 underline-offset-4">{{ $service->get('title') }}</h3>
            <x-icon name="solar:arrow-right-linear" class="size-4 text-slate-400 group-hover:text-slate-600 transition-colors shrink-0" />
        </div>
        <p class="text-slate-600 text-sm mt-0.5 leading-relaxed">{{ $service->get($expanded ? 'excerpt' : 'description') }}</p>
    </div>
</a>
