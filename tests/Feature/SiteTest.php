<?php

namespace Tests\Feature;

use App\Site\SiteData;
use DOMDocument;
use DOMXPath;
use Statamic\Facades\Entry;
use Tests\TestCase;

class SiteTest extends TestCase
{
    public function test_all_original_pages_preserve_their_content_links_and_metadata(): void
    {
        $baseline = json_decode(file_get_contents(base_path('tests/Fixtures/astro-baseline.json')), true);
        foreach ($baseline as $route => $expected) {
            $response = $this->get($route)->assertStatus($route === '/404.html' ? 404 : 200);
            $xpath = $this->xpath($response->getContent());
            $this->assertSame($expected['title'], $xpath->evaluate('string(//title)'), "$route title");
            $this->assertSame($expected['description'], $xpath->evaluate('string(//meta[@name="description"]/@content)'), "$route description");
            if ($route !== '/404.html') {
                $this->assertSame($expected['canonical'], $xpath->evaluate('string(//link[@rel="canonical"]/@href)'), "$route canonical");
            }
            $this->assertSame($expected['image'], $xpath->evaluate('string(//meta[@property="og:image"]/@content)'), "$route social image");
            $texts = [];
            foreach ($xpath->query('//main//*[self::h1 or self::h2 or self::h3 or (self::p and not(ancestor::li) and not(ancestor::td) and not(ancestor::th))]') as $node) {
                $texts[] = trim(preg_replace('/\s+/u', ' ', $node->textContent));
            }
            $this->assertSame($expected['texts'], $texts, "$route text content");
            $links = [];
            foreach ($xpath->query('//main//a[@href]') as $node) {
                $links[] = $node->getAttribute('href');
            }
            $this->assertSame($expected['links'], $links, "$route links");
        }
    }

    public function test_drafts_are_excluded_from_routes_lists_sitemap_and_social_images(): void
    {
        foreach (['how-i-use-ai-coding-agents', 'lessons-from-7-years-as-a-technical-lead'] as $slug) {
            $this->get('/articles/'.$slug.'/')->assertNotFound();
            $this->get('/articles/')->assertDontSee($slug);
            $this->get('/')->assertDontSee($slug);
            $this->get('/sitemap-0.xml')->assertDontSee($slug);
            $this->assertFileDoesNotExist(public_path('og/articles/'.$slug.'.png'));
        }
        $this->get('/missing-page/')->assertNotFound()->assertSee('noindex, follow');
        $this->get('/articles/2/')->assertNotFound();
    }

    public function test_pagination_uses_path_urls_and_handles_boundaries(): void
    {
        $draft = Entry::query()->where('slug', 'how-i-use-ai-coding-agents')->first();
        // In-memory mutation only: never publish or write the repository fixture.
        $draft->published(true);
        try {
            $this->get('/articles/')->assertOk()->assertSee('href="/articles/2/"', false);
            $response = $this->get('/articles/2/')->assertOk()->assertSee('6 Raycast extensions for Laravel developers')->assertDontSee('The Uncomfortable Middle');
            $response->assertSee('https://andrebreia.dev/articles/2/')->assertSee('href="/articles/"', false);
            $this->get('/articles/3/')->assertNotFound();
        } finally {
            $draft->published(false);
        }
    }

    public function test_bard_renders_lists_tables_links_and_horizontal_rules(): void
    {
        $xpath = $this->xpath($this->get('/articles/building-a-saas-mvp-with-laravel/')->assertOk()->getContent());
        $this->assertSame(1, $xpath->query('//article//table')->length);
        $this->assertSame(10, $xpath->query('//article//table//tr')->length);
        $this->assertSame(6, $xpath->query('//article//ol/li')->length);
        $this->get('/articles/the-uncomfortable-middle/')->assertSee('<hr', false)->assertSee('href="/articles/going-full-time-freelance/"', false);
    }

    public function test_service_faq_schema_matches_the_editable_body(): void
    {
        $xpath = $this->xpath($this->get('/services/api-integrations/')->assertOk()->getContent());
        $schema = json_decode($xpath->evaluate('string(//script[@type="application/ld+json"])'), true);
        $faq = collect($schema['@graph'])->firstWhere('@type', 'FAQPage');
        $this->assertSame('Can you work with an API that has poor documentation?', $faq['mainEntity'][0]['name']);
        $this->assertCount(2, $faq['mainEntity']);
        $this->get('/sitemap-0.xml')->assertSee('<lastmod>2026-06-03</lastmod>', false)->assertDontSee('not-found');
    }

    public function test_solo_and_production_read_only_configuration(): void
    {
        $this->assertFalse(config('statamic.editions.pro'));
        $this->assertFalse(config('statamic.cp.enabled'));
        $this->get('/cp')->assertNotFound();
        $this->assertSame('file', config('statamic.users.repository'));
    }

    public function test_every_entry_has_a_valid_blueprint_and_bard_round_trips(): void
    {
        $this->assertCount(22, Entry::all());
        foreach (Entry::all() as $entry) {
            $this->assertNotNull($entry->blueprint(), $entry->slug());
            if (! $entry->get('body')) {
                continue;
            }
            $fields = $entry->blueprint()->fields()->addValues($entry->data()->all());
            $processed = $fields->preProcess()->values()->get('body');
            $roundTrip = $entry->blueprint()->fields()->addValues(['body' => $processed])->process()->values()->get('body');
            $fieldtype = $entry->blueprint()->field('body')->fieldtype();
            $this->assertSame((string) $entry->augmentedValue('body')->value(), (string) $fieldtype->augment($roundTrip), $entry->slug());
            $this->assertStringContainsString('<p>', (string) $entry->augmentedValue('body')->value(), $entry->slug());
        }
        $this->assertCount(5, SiteData::globals('navigation')['items']);
    }

    private function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);

        return new DOMXPath($dom);
    }
}
