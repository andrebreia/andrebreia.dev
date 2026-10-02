<?php

use App\Http\Controllers\SitemapController;
use App\ViewModels\SiteViewModel;
use Illuminate\Support\Facades\Route;

Route::statamic('articles/{pageNumber}', 'articles', [
    'load' => '/articles',
    'layout' => false,
    'view_model' => SiteViewModel::class,
])->where('pageNumber', '[2-9]|[1-9][0-9]+');

Route::get('sitemap-index.xml', [SitemapController::class, 'index']);
Route::get('sitemap-0.xml', [SitemapController::class, 'pages']);
Route::get('404.html', fn () => abort(404));
