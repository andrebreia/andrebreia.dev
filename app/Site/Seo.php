<?php

namespace App\Site;

use Carbon\Carbon;
use Statamic\Contracts\Entries\Entry;

class Seo
{
    public static function forEntry(Entry $entry, array $site, int $pageNumber): array
    {
        $base = rtrim($site['url'], '/');
        $language = SiteData::language($entry);
        $labels = SiteData::globals('labels', $language);
        $home = $language === 'fr' ? '/fr/' : '/';
        $collection = $entry->collectionHandle();
        $isPage = $collection === 'pages';
        $path = SiteData::url($entry, $pageNumber);
        $canonical = $base.$path;
        $title = $entry->get('title');
        if (! $isPage) {
            $title = ($entry->get('seoTitle') ?: $title).' – '.$site['name'];
        }
        if ($pageNumber > 1) {
            $title = str_replace(' – '.$site['name'], ' – Page '.$pageNumber.' – '.$site['name'], $title);
        }
        $image = $base.($isPage ? ($language === 'fr' ? '/og-image-fr.png' : '/og-image.png') : '/og/'.$collection.'/'.$entry->slug().'.png');
        $alternates = [];
        if ($pageNumber === 1 && $entry->template() !== 'not-found' && ($other = SiteData::counterpart($entry))) {
            $alternates = [$language => $canonical, SiteData::language($other) => $base.SiteData::url($other)];
            $alternates['x-default'] = $alternates['en'];
        }
        $person = ['@id' => $base.'/#person'];
        $profile = SiteData::page('about', $language);
        $authorUrl = $profile?->published() ? SiteData::url($profile) : $home;
        $publishedAt = $collection === 'articles' ? $entry->date()?->toISOString() : null;
        $modifiedAt = $collection === 'articles' && $entry->get('dateModified')
            ? Carbon::parse($entry->get('dateModified'))->toISOString() : null;
        $sameAs = array_column($site['socialLinks'], 'href');
        $graph = [
            [
                '@type' => 'Person', ...$person, 'name' => $site['name'], 'url' => $base.$authorUrl,
                'image' => $base.'/images/'.$site['portrait'],
                'jobTitle' => $site['jobTitle'], 'knowsAbout' => $site['knowsAbout'],
                'hasCredential' => array_map(fn ($item) => [
                    '@type' => 'EducationalOccupationalCredential', 'name' => $item['title'],
                    'credentialCategory' => 'Professional Certification',
                ], SiteData::globals('certifications', $language)['items']),
                'sameAs' => $sameAs,
            ],
            ['@type' => 'WebSite', '@id' => $base.'/#website', 'name' => $site['name'], 'url' => $base.'/', 'inLanguage' => ['en', 'fr'], 'publisher' => $person],
            array_filter([
                '@type' => match ($entry->template()) {
                    'about' => 'ProfilePage',
                    'articles', 'services' => 'CollectionPage',
                    default => 'WebPage',
                },
                '@id' => $canonical.'#webpage', 'url' => $canonical,
                'name' => $title, 'description' => $entry->get($isPage ? 'description' : 'excerpt'),
                'inLanguage' => $language, 'isPartOf' => ['@id' => $base.'/#website'],
                'mainEntity' => match (true) {
                    $entry->template() === 'about' => $person,
                    $collection === 'articles' => ['@id' => $canonical.'#article'],
                    $collection === 'projects' => ['@id' => $canonical.'#project'],
                    $collection === 'services' => ['@id' => $canonical.'#service'],
                    default => null,
                },
            ], fn ($value) => $value !== null),
        ];
        $crumbs = [['name' => $labels['home'], 'path' => $home]];
        if (in_array($collection, ['articles', 'services'])) {
            $landing = SiteData::page($collection, $language);
            if ($landing?->published()) {
                $crumbs[] = ['name' => $labels[$collection], 'path' => SiteData::url($landing)];
            }
        }
        if (! $isPage) {
            $crumbs[] = ['name' => $entry->get('title'), 'path' => $path];
        }
        if (! $isPage || $entry->template() === 'services') {
            if ($isPage) {
                $crumbs[] = ['name' => $labels['services'], 'path' => $path];
            }
            $graph[] = ['@type' => 'BreadcrumbList', 'itemListElement' => array_map(fn ($item, $index) => [
                '@type' => 'ListItem', 'position' => $index + 1, 'name' => $item['name'], 'item' => $base.$item['path'],
            ], $crumbs, array_keys($crumbs))];
        }
        if ($collection === 'articles') {
            $graph[] = array_filter([
                '@type' => 'BlogPosting', '@id' => $canonical.'#article', 'headline' => $entry->get('title'),
                'description' => $entry->get('excerpt'), 'inLanguage' => $language, 'datePublished' => $publishedAt,
                'dateModified' => $modifiedAt,
                'url' => $canonical, 'mainEntityOfPage' => ['@id' => $canonical.'#webpage'], 'author' => $person, 'publisher' => $person,
            ], fn ($value) => $value !== null);
        } elseif ($collection === 'projects') {
            $graph[] = array_filter([
                '@type' => 'CreativeWork', '@id' => $canonical.'#project', 'name' => $entry->get('title'),
                'description' => $entry->get('excerpt'), 'inLanguage' => $language, 'url' => $canonical,
                'creator' => $person, 'sameAs' => (string) $entry->augmentedValue('external_url')->value(),
            ]);
        } elseif ($collection === 'services') {
            $graph[] = [
                '@type' => 'Service', '@id' => $canonical.'#service', 'name' => $entry->get('headline') ?: $entry->get('title'),
                'description' => $entry->get('excerpt'), 'url' => $canonical, 'serviceType' => $entry->get('title'),
                'areaServed' => 'Worldwide', 'provider' => $person,
            ];
        }
        // Derive service FAQs from the same Bard nodes shown on the page.
        $faqs = $entry->get('faqs', []);
        if ($collection === 'services') {
            $nodes = $entry->get('body', []);
            foreach ($nodes as $index => $node) {
                if ($node['type'] !== 'heading' || ($node['attrs']['level'] ?? 0) !== 3) {
                    continue;
                }
                $question = trim(implode('', array_column($node['content'] ?? [], 'text')));
                for ($next = $index + 1; ($nodes[$next]['type'] ?? '') === 'paragraph'; $next++) {
                    $answer = trim(implode('', array_column($nodes[$next]['content'] ?? [], 'text')));
                    if ($answer !== '') {
                        if ($question !== '') {
                            $faqs[] = ['question' => $question, 'answer' => $answer];
                        }
                        break;
                    }
                }
            }
        }
        if ($faqs) {
            $graph[] = ['@type' => 'FAQPage', 'mainEntity' => array_map(fn ($faq) => [
                '@type' => 'Question', 'name' => $faq['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
            ], $faqs)];
        }

        return [
            'title' => $title, 'description' => $entry->get($isPage ? 'description' : 'excerpt'),
            'canonical' => $entry->template() === 'not-found' ? null : $canonical,
            'image' => $image, 'type' => $collection === 'articles' ? 'article' : 'website',
            'imageAlt' => $isPage ? $site['ogTitle'] : ($entry->get('headline') ?: $entry->get('title')),
            'authorUrl' => $authorUrl, 'publishedAt' => $publishedAt, 'modifiedAt' => $modifiedAt,
            'alternates' => $alternates,
            'robots' => $entry->template() === 'not-found' || ! $entry->published() ? 'noindex, follow' : 'max-image-preview:large',
            'jsonLd' => ['@context' => 'https://schema.org', '@graph' => $entry->template() === 'not-found' ? [] : $graph],
        ];
    }
}
