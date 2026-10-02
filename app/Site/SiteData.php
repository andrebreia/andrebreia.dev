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
    public static function page(string $slug): EntryContract
    {
        return Entry::query()->where('collection', 'pages')->where('slug', $slug)->first();
    }

    public static function globals(string $handle): array
    {
        $globals = GlobalSet::findByHandle($handle)->inCurrentSite();

        return self::viewData($globals->data()->all(), $globals->blueprint()->fields(), $globals);
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

    public static function published(string $collection): Builder
    {
        return Entry::query()->where('collection', $collection)->where('published', true);
    }

    public static function forEntry(EntryContract $entry, int $pageNumber = 1): array
    {
        $content = $entry instanceof Page ? $entry->entry() : $entry;
        $site = self::globals('site');
        $articles = self::published('articles')->orderBy('date', 'desc')->orderBy('slug')->get();
        $totalPages = max(1, (int) ceil($articles->count() / 4));
        abort_if($pageNumber < 1 || $pageNumber > $totalPages, 404);

        return [
            'entry' => $entry,
            'pageData' => self::viewData($entry->data()->all(), $content->blueprint()->fields(), $content),
            'bodyHtml' => $entry->get('body') ? RichText::render((string) $entry->augmentedValue('body')->value()) : '',
            'siteData' => $site,
            'cta' => self::globals('cta'),
            'labels' => self::globals('labels'),
            'navigation' => self::globals('navigation')['items'],
            'experience' => self::globals('experience')['items'],
            'certifications' => self::globals('certifications')['items'],
            'testimonials' => self::globals('testimonials')['items'],
            'services' => self::published('services')->orderBy('order')->get(),
            'projects' => self::published('projects')->orderBy('year', 'desc')->orderBy('slug')->get(),
            'latestArticles' => $articles->take(2),
            'articles' => $articles->slice(($pageNumber - 1) * 4, 4),
            'pageNumber' => $pageNumber,
            'totalPages' => $totalPages,
            'seo' => Seo::forEntry($entry, $site, $pageNumber),
        ];
    }
}
