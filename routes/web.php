<?php

use App\Http\Controllers\SitemapController;
use App\Site\SiteData;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Statamic\Facades\Entry;

foreach (['/articles', '/fr/articles'] as $path) {
    Route::get($path.'/{pageNumber}', function (string $pageNumber) use ($path): View {
        $entry = Entry::findByUri($path);
        abort_if(! $entry || ! $entry->published(), 404);

        return view('articles', SiteData::forEntry($entry, (int) $pageNumber));
    })->where('pageNumber', '[2-9]|[1-9][0-9]+')->middleware('statamic.web');
}

Route::get('sitemap-index.xml', [SitemapController::class, 'index']);
Route::get('sitemap-0.xml', [SitemapController::class, 'pages']);
Route::get('404.html', fn () => abort(404));
