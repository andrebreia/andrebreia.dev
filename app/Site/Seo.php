<?php

namespace App\Site;

use Carbon\Carbon;
use Statamic\Contracts\Entries\Entry;

class Seo
{
    public static function forEntry(Entry $entry, array $site, int $pageNumber): array
    {
        $base = rtrim($site['url'], '/');
        $collection = $entry->collectionHandle();
        $isPage = $collection === 'pages';
        $path = rtrim($entry->url() ?? '', '/').'/';
        if ($pageNumber > 1) {
            $path .= $pageNumber.'/';
        }
        $canonical = $base.$path;
        $title = $entry->get('title');
        if (! $isPage) {
            $title = ($collection === 'services' ? ($entry->get('seoTitle') ?: $title) : $title).' – '.$site['name'];
        }
        if ($pageNumber > 1) {
            $title = str_replace(' – '.$site['name'], ' – Page '.$pageNumber.' – '.$site['name'], $title);
        }
        $image = $base.($isPage ? '/og-image.png' : '/og/'.$collection.'/'.$entry->slug().'.png');
        $person = ['@id' => $base.'/#person'];
        $sameAs = array_column($site['socialLinks'], 'href');
        $graph = [
            [
                '@type' => 'Person', ...$person, 'name' => $site['name'], 'url' => $base,
                'jobTitle' => $site['jobTitle'], 'knowsAbout' => $site['knowsAbout'],
                'hasCredential' => array_map(fn ($item) => [
                    '@type' => 'EducationalOccupationalCredential', 'name' => $item['title'],
                    'credentialCategory' => 'Professional Certification',
                ], SiteData::globals('certifications')['items']),
                'sameAs' => $sameAs,
            ],
            ['@type' => 'WebSite', '@id' => $base.'/#website', 'name' => $site['name'], 'url' => $base, 'publisher' => $person],
        ];
        $crumbs = [['name' => 'Home', 'path' => '/']];
        if (in_array($collection, ['articles', 'services'])) {
            $crumbs[] = ['name' => ucfirst($collection), 'path' => '/'.$collection.'/'];
        }
        if (! $isPage) {
            $crumbs[] = ['name' => $entry->get('title'), 'path' => $path];
        }
        if (! $isPage || $entry->slug() === 'services') {
            if ($isPage) {
                $crumbs[] = ['name' => 'Services', 'path' => '/services/'];
            }
            $graph[] = ['@type' => 'BreadcrumbList', 'itemListElement' => array_map(fn ($item, $index) => [
                '@type' => 'ListItem', 'position' => $index + 1, 'name' => $item['name'], 'item' => $base.$item['path'],
            ], $crumbs, array_keys($crumbs))];
        }
        if ($collection === 'articles') {
            $graph[] = array_filter([
                '@type' => 'BlogPosting', '@id' => $canonical.'#article', 'headline' => $entry->get('title'),
                'description' => $entry->get('excerpt'), 'datePublished' => $entry->date()?->toISOString(),
                'dateModified' => $entry->get('dateModified') ? Carbon::parse($entry->get('dateModified'))->toISOString() : null,
                'url' => $canonical, 'mainEntityOfPage' => $canonical, 'image' => $image, 'author' => $person, 'publisher' => $person,
            ], fn ($value) => $value !== null);
        } elseif ($collection === 'projects') {
            $graph[] = array_filter([
                '@type' => 'CreativeWork', '@id' => $canonical.'#project', 'name' => $entry->get('title'),
                'description' => $entry->get('excerpt'), 'url' => $canonical, 'dateCreated' => $entry->get('year'),
                'creator' => $person, 'sameAs' => (string) $entry->augmentedValue('external_url')->value(),
            ]);
        } elseif ($collection === 'services') {
            $graph[] = [
                '@type' => 'Service', '@id' => $canonical.'#service', 'name' => $entry->get('headline') ?: $entry->get('title'),
                'description' => $entry->get('excerpt'), 'url' => $canonical, 'serviceType' => $entry->get('title'),
                'areaServed' => 'Worldwide', 'provider' => $person,
            ];
        } elseif ($entry->slug() === 'services') {
            $graph[] = [
                '@type' => 'ProfessionalService', '@id' => $base.'/#professional-service',
                'name' => $site['name'].' Freelance Development', 'url' => $base, 'description' => $site['description'],
                'areaServed' => 'Worldwide', 'founder' => $person, 'sameAs' => $sameAs,
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
            'canonical' => $canonical, 'image' => $image, 'type' => $collection === 'articles' ? 'article' : 'website',
            'robots' => $entry->slug() === 'not-found' ? 'noindex, follow' : null,
            'jsonLd' => ['@context' => 'https://schema.org', '@graph' => $graph],
        ];
    }
}
