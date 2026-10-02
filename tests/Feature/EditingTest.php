<?php

use App\Site\RichText;
use App\Site\Seo;
use App\Site\SiteData;
use Illuminate\Cache\RedisStore;
use Illuminate\Http\Request;
use Illuminate\Routing\Events\ResponsePrepared;
use Statamic\Facades\Entry;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\YAML;
use Statamic\StaticCaching\StaticCacheManager;

it('uses the shared default Redis store for static caching', function () {
    config(['cache.default' => 'redis', 'statamic.static_caching.strategy' => 'half']);
    $manager = new StaticCacheManager(app());
    expect($manager->cacheStore()->getStore())->toBeInstanceOf(RedisStore::class);
    expect($manager->cacheStore())->toBe(app('cache')->store('redis'));
});

it('shares half-cache responses and invalidation without clearing unrelated cache data', function () {
    config(['statamic.static_caching.strategy' => 'half']);
    $writer = new StaticCacheManager(app());
    $reader = new StaticCacheManager(app());
    $request = Request::create('https://andrebreia.dev/cache-test');
    $writer->cacheStore()->put('unrelated', 'preserved');
    $writer->driver()->cachePage($request, '<p>Cached response</p>');
    event(new ResponsePrepared($request, response('<p>Cached response</p>')));
    expect($reader->driver()->getCachedPage($request)->content)->toBe('<p>Cached response</p>');
    $writer->flush();
    expect($reader->driver()->hasCachedPage($request))->toBeFalse();
    expect($writer->cacheStore()->get('unrelated'))->toBe('preserved');
});

it('resolves nested entry links without augmenting asset paths or Markdown', function () {
    $uses = clone SiteData::page('uses');
    $sections = $uses->get('sections');
    $sections[0]['items'][0]['link'] = 'entry::'.SiteData::page('about')->id();
    $sections[0]['items'][0]['text'] = '**Raw Markdown**';
    $uses->set('sections', $sections);
    $data = SiteData::forEntry($uses);
    expect($data['pageData']['sections'][0]['items'][0]['link'])->toBe('/about');
    expect($data['pageData']['sections'][0]['items'][0]['text'])->toBe('**Raw Markdown**');
    expect($data['siteData']['portrait'])->toBe('andre.jpg');
    expect(view('uses', $data)->render())->toContain('href="/about"')->not->toContain('entry::');

    $project = clone Entry::query()->where('collection', 'projects')->first();
    $project->set('external_url', 'entry::'.SiteData::page('about')->id());
    $data = SiteData::forEntry($project);
    expect(view('project', $data)->render())->toContain('href="/about"')->not->toContain('entry::');
    expect(collect($data['seo']['jsonLd']['@graph'])->firstWhere('@type', 'CreativeWork')['sameAs'])->toBe('/about');
});

it('resolves global links and renders with an omitted optional booking URL', function () {
    $globals = GlobalSet::findByHandle('site')->inCurrentSite();
    $original = $globals->data()->all();
    try {
        $edited = $original;
        unset($edited['bookingUrl']);
        $edited['socialLinks'][0]['href'] = 'entry::'.SiteData::page('about')->id();
        $globals->data($edited);
        expect(SiteData::globals('site')['socialLinks'][0]['href'])->toBe('/about');
        foreach (['/', '/about/'] as $url) {
            $this->get($url)->assertOk()->assertSee('Send an email')->assertDontSee('Schedule a call');
        }
    } finally {
        $globals->data($original);
    }
});

it('ignores incomplete FAQs and skips blank Bard separators', function () {
    $service = clone Entry::query()->where('collection', 'services')->first();
    $heading = fn ($text) => ['type' => 'heading', 'attrs' => ['level' => 3], 'content' => [['type' => 'text', 'text' => $text]]];
    $service->set('body', [
        ['type' => 'heading', 'attrs' => ['level' => 3]],
        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Orphan answer']]],
        $heading('Unanswered?'), ['type' => 'paragraph'],
        $heading('Can you help?'), ['type' => 'paragraph'],
        ['type' => 'paragraph', 'content' => [
            ['type' => 'text', 'text' => 'Yes, '],
            ['type' => 'text', 'text' => 'with Laravel.', 'marks' => [['type' => 'bold']]],
        ]],
        $heading('Also unanswered?'), ['type' => 'paragraph'],
    ]);
    $data = SiteData::forEntry($service);
    $faq = collect($data['seo']['jsonLd']['@graph'])->firstWhere('@type', 'FAQPage');
    expect($faq['mainEntity'])->toHaveCount(1);
    expect($faq['mainEntity'][0]['name'])->toBe('Can you help?');
    expect($faq['mainEntity'][0]['acceptedAnswer']['text'])->toBe('Yes, with Laravel.');
    expect(view('service', $data)->render())->toContain('Can you help?');
});

it('renders a newly created page using the default Blade and Bard layout', function () {
    $page = Entry::make()->collection('pages')->slug('new-page')->data([
        'title' => 'New page title', 'heading' => 'New page heading',
        'body' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'New page body.']]]],
    ]);
    expect($page->template())->toBe('page');
    expect($page->blueprint()->field('template')->get('default'))->toBe('page');
    expect($page->blueprint()->field('body')->get('if_any'))->toBe(['template' => ['about', 'page']]);
    expect(view($page->template(), SiteData::forEntry($page))->render())
        ->toContain('New page heading', 'New page body.')->not->toContain('Certifications');
});

it('does not offer an unused article SEO title override', function () {
    $article = Entry::query()->where('slug', 'the-uncomfortable-middle')->first();
    expect($article->blueprint()->fields()->all()->has('seoTitle'))->toBeFalse();
    expect(Seo::forEntry($article, SiteData::globals('site'), 1)['title'])
        ->toBe('The Uncomfortable Middle – André Breia');
});

it('renders all pages after empty global grids are serialized and reloaded', function () {
    $globals = collect(['testimonials', 'certifications', 'navigation'])
        ->mapWithKeys(fn ($handle) => [$handle => GlobalSet::findByHandle($handle)->inCurrentSite()]);
    $originals = $globals->map(fn ($variables) => $variables->data()->all());
    try {
        foreach ($globals as $handle => $variables) {
            $variables->set('items', []);
            $reloaded = YAML::parse($variables->fileContents());
            expect($reloaded)->not->toHaveKey('items');
            $variables->data($reloaded);
            expect(SiteData::globals($handle)['items'])->toBe([]);
        }
        foreach (array_keys(json_decode(file_get_contents(base_path('tests/Fixtures/astro-baseline.json')), true)) as $url) {
            $this->get($url)->assertStatus($url === '/404.html' ? 404 : 200);
        }
        $data = SiteData::forEntry(SiteData::page('home'));
        expect(collect($data['seo']['jsonLd']['@graph'])->firstWhere('@type', 'Person')['hasCredential'])->toBe([]);
        $this->get('/about/')->assertDontSee('Certifications');
    } finally {
        foreach ($globals as $handle => $variables) {
            $variables->data($originals[$handle]);
        }
    }
});

it('renders omitted optional page fields after serialization', function ($slug, $fields) {
    $page = clone SiteData::page($slug);
    foreach ($fields as $field) {
        $page->set($field, null);
    }
    $reloaded = YAML::parse($page->fileContents());
    foreach ($fields as $field) {
        expect($reloaded)->not->toHaveKey($field);
    }
    $page->data($reloaded);
    $html = view($page->template(), SiteData::forEntry($page))->render();
    expect($html)->toContain(e($page->get('heading')))->not->toContain('<p class="text-sm text-slate-600"></p>');
})->with([
    ['articles', ['intro']],
    ['uses', ['intro', 'sections']],
    ['services', ['intro', 'projects', 'faqs']],
    ['home', ['bio', 'show_testimonials', 'services_label']],
]);

it('stores block content in every Bard list item and table cell', function () {
    $checked = 0;
    $check = function ($nodes) use (&$check, &$checked) {
        foreach ($nodes as $node) {
            if (in_array($node['type'], ['listItem', 'tableHeader', 'tableCell'])) {
                expect($node['content'][0]['type'])->toBe('paragraph');
                foreach ($node['content'] as $child) {
                    expect($child['type'])->not->toBeIn(['text', 'hardBreak']);
                }
                $checked++;
            }
            $check($node['content'] ?? []);
        }
    };
    foreach (Entry::all() as $entry) {
        $check($entry->get('body', []));
    }
    expect($checked)->toBeGreaterThan(20);
    expect(config('statamic.templates.language'))->toBe('blade');
});

it('keeps single-paragraph lists tight without flattening multi-paragraph content', function () {
    $html = RichText::render('<ul><li><p>One <strong>item</strong></p></li><li><p>First</p><p>Second</p></li></ul><table><tbody><tr><td><p>Cell</p></td></tr></tbody></table>');
    expect($html)->toContain('<li>One <strong>item</strong></li>', '<li><p>First</p><p>Second</p></li>', '<td>Cell</td>');
});
