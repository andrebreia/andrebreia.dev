@props(['icon', 'label'])
<h2 class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-6 flex items-center gap-2">
    <x-icon :name="$icon" class="size-4" />{{ $label }}
</h2>
