<?php

namespace App\Site;

use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Contracts\Globals\Variables;
use Statamic\Contracts\Query\Builder;
use Statamic\Facades\Entry;
use Statamic\Facades\GlobalSet;
use Statamic\Fields\Fields;
use Statamic\Structures\Page;

class SiteData
{
    public static function language(EntryContract $entry): string
    {
        return $entry->get('content_language', 'en');
    }

    public static function requestLanguage(): string
    {
        return request()->is('fr', 'fr/*') ? 'fr' : 'en';
    }

    public static function counterpart(EntryContract $entry): ?EntryContract
    {
        if (! $entry->published()) {
            return null;
        }

        $other = self::language($entry) === 'fr'
            ? Entry::find($entry->get('translation_of'))
            : self::published($entry->collectionHandle(), 'fr')->where('translation_of', $entry->id())->first();

        return $other?->published() && $other->url()
            && self::language($other) === (self::language($entry) === 'fr' ? 'en' : 'fr')
            && $other->collectionHandle() === $entry->collectionHandle() ? $other : null;
    }

    public static function page(string $slug, string $language = 'en'): ?EntryContract
    {
        if ($slug === 'articles' && $language === 'fr') {
            return Entry::query()->where('collection', 'pages')->where('content_language', 'fr')->where('french_url', '/fr/articles')->first();
        }

        $page = Entry::query()->where('collection', 'pages')->where('slug', $slug)->first();
        if (! $page) {
            return null;
        }

        return $language === 'fr'
            ? Entry::query()->where('collection', 'pages')->where('content_language', 'fr')->where('translation_of', $page->id())->first()
            : $page;
    }

    public static function url(EntryContract $entry, int $pageNumber = 1): string
    {
        return rtrim($entry->url() ?? '', '/').'/'.($pageNumber > 1 ? $pageNumber.'/' : '');
    }

    public static function globals(string $handle, string $language = 'en'): array
    {
        $globals = GlobalSet::findByHandle(($language === 'fr' ? 'fr_' : '').$handle)->inCurrentSite();
        $data = $globals->data()->all();
        if ($handle === 'site' && $language === 'fr') {
            $data = array_replace(self::globals('site'), $data);
        }

        return self::viewData($data, $globals->blueprint()->fields(), $globals);
    }

    /** Restore omitted display values and resolve links, keeping assets and rich text raw. */
    private static function viewData(array $data, Fields $fields, EntryContract|Variables $parent): array
    {
        foreach ($fields->setParent($parent)->addValues($data)->all() as $field) {
            $handle = $field->handle();
            if ($field->type() === 'link' && isset($data[$handle])) {
                $data[$handle] = (string) $field->fieldtype()->augment($data[$handle]);
            } elseif ($field->type() === 'grid') {
                $data[$handle] = array_map(fn ($row) => self::viewData(
                    $row, new Fields($field->get('fields')), $parent
                ), $data[$handle] ?? []);
            } elseif (in_array($field->type(), ['text', 'textarea', 'toggle'])) {
                $data[$handle] ??= $field->get('default') ?? ($field->type() === 'toggle' ? false : '');
            }
        }

        return $data;
    }

    public static function published(string $collection, string $language = 'en'): Builder
    {
        return Entry::query()->where('collection', $collection)->where('published', true)
            ->where('content_language', $language === 'fr' ? '=' : '!=', 'fr');
    }

    public static function forEntry(EntryContract $entry, int $pageNumber = 1): array
    {
        $content = $entry instanceof Page ? $entry->entry() : $entry;
        $language = self::language($entry);
        app()->setLocale($language);
        $site = self::globals('site', $language);
        $articles = self::published('articles', $language)->orderBy('date', 'desc')->orderBy('slug')->get();
        $totalPages = max(1, (int) ceil($articles->count() / 4));
        abort_if($pageNumber < 1 || $pageNumber > $totalPages, 404);
        $counterpart = self::counterpart($entry);
        $articlesPage = self::page('articles', $language);
        $homeUrl = $language === 'fr' ? '/fr/' : '/';
        $navigation = array_filter(self::globals('navigation', $language)['items'], function (array $link): bool {
            $target = Entry::findByUri(rtrim($link['href'], '/') ?: '/');

            return ! $target || $target->published();
        });

        return [
            'entry' => $entry,
            'language' => $language,
            'homeUrl' => $homeUrl,
            'articlesUrl' => $articlesPage?->published() ? self::url($articlesPage) : $homeUrl,
            'languageUrl' => $counterpart ? self::url($counterpart) : ($language === 'fr' ? '/' : '/fr/'),
            'hasTranslation' => (bool) $counterpart,
            'pageData' => self::viewData($entry->data()->all(), $content->blueprint()->fields(), $content),
            'bodyHtml' => $entry->get('body') ? RichText::render((string) $entry->augmentedValue('body')->value()) : '',
            'siteData' => $site,
            'cta' => self::globals('cta', $language),
            'labels' => self::globals('labels', $language),
            'navigation' => $navigation,
            'experience' => self::globals('experience', $language)['items'],
            'certifications' => self::globals('certifications', $language)['items'],
            'testimonials' => self::globals('testimonials', $language)['items'],
            'services' => self::published('services', $language)->orderBy('order')->get(),
            'projects' => self::published('projects', $language)->orderBy('year', 'desc')->orderBy('slug')->get(),
            'latestArticles' => $articles->take(2),
            'articles' => $articles->slice(($pageNumber - 1) * 4, 4),
            'pageNumber' => $pageNumber,
            'totalPages' => $totalPages,
            'seo' => Seo::forEntry($entry, $site, $pageNumber),
        ];
    }
}
