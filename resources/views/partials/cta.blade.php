<section class="bg-slate-50/50 -mx-4 px-4 py-8 rounded-2xl border border-slate-100 mt-14">
    <h2 class="text-slate-900 font-medium text-lg mb-2 font-heading">{{ $cta['heading'] }}</h2>
    <p class="text-sm text-slate-600 leading-relaxed mb-6 max-w-md">{{ $cta['description'] }}</p>
    @include('partials.buttons', ['position' => 'cta-section'])
</section>
