<?php

use App\Http\Controllers\AgentContentController;
use App\Http\Controllers\SitemapController;
use App\Site\SiteData;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Statamic\Facades\Entry;
use Statamic\StaticCaching\Middleware\Cache;

foreach (['/articles', '/fr/articles'] as $path) {
    Route::get($path.'/{pageNumber}', function (string $pageNumber) use ($path): View {
        $entry = Entry::findByUri($path);
        abort_if(! $entry || ! $entry->published(), 404);

        return view('articles', SiteData::forEntry($entry, (int) $pageNumber));
    })->where('pageNumber', '[2-9]|[1-9][0-9]+')->middleware('statamic.web')->name('articles.'.($path === '/articles' ? 'en' : 'fr'));
}

Route::get('llms.txt', [AgentContentController::class, 'index'])->name('agent.index');
Route::get('.well-known/agent-skills/index.json', [AgentContentController::class, 'skills'])->name('agent.skills');
Route::get('.well-known/agent-skills/reading-andre-breia/SKILL.md', [AgentContentController::class, 'skill'])->name('agent.skill');
Route::get('.well-known/ai-catalog.json', [AgentContentController::class, 'catalog'])->name('agent.catalog');
Route::get('.well-known/ard.json', [AgentContentController::class, 'catalog'])->name('agent.ard');
Route::middleware('statamic.web')->withoutMiddleware(Cache::class)->group(function (): void {
    Route::get('index.md', [AgentContentController::class, 'show'])->name('agent.markdown.home');
    Route::get('{path}/index.md', [AgentContentController::class, 'show'])
        ->where('path', '.*')->name('agent.markdown');
});

Route::get('sitemap-index.xml', [SitemapController::class, 'index']);
Route::get('sitemap-0.xml', [SitemapController::class, 'pages']);
Route::get('404.html', fn () => abort(404));
