<?php

namespace App\Http\Controllers;

use App\Site\SiteData;
use Illuminate\Http\Response;
use Statamic\Facades\Entry;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $base = rtrim(SiteData::globals('site')['url'], '/');

        return response('<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><sitemap><loc>'.e($base.'/sitemap-0.xml').'</loc></sitemap></sitemapindex>', 200, ['Content-Type' => 'application/xml']);
    }

    public function pages(): Response
    {
        $base = rtrim(SiteData::globals('site')['url'], '/');
        $urls = Entry::query()->where('published', true)->get()
            ->filter(fn ($entry) => $entry->url() && $entry->slug() !== 'not-found')
            ->map(function ($entry) use ($base) {
                $lastmod = $entry->collectionHandle() === 'articles' ? ($entry->get('dateModified') ?: $entry->date()?->toDateString()) : null;

                return '<url><loc>'.e($base.rtrim($entry->url(), '/').'/').'</loc>'.($lastmod ? '<lastmod>'.e($lastmod).'</lastmod>' : '').'</url>';
            });
        $pages = (int) ceil(SiteData::published('articles')->count() / 4);
        for ($page = 2; $page <= $pages; $page++) {
            $urls->push('<url><loc>'.e($base.'/articles/'.$page.'/').'</loc></url>');
        }

        return response('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$urls->implode('').'</urlset>', 200, ['Content-Type' => 'application/xml']);
    }
}
