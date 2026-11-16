<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use Illuminate\Support\Facades\File;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;
use Statamic\Facades\User;
use Statamic\StaticCaching\StaticCacheManager;

function localizationXPath(string $html): DOMXPath
{
    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="utf-8" ?>'.$html);

    return new DOMXPath($document);
}

it('serves each French translation at its own URL with reciprocal metadata and shared assets', function () {
    $translations = Entry::query()->where('content_language', 'fr')->where('published', true)->get();
    expect($translations)->toHaveCount(19);
    $sitemap = $this->get('/sitemap-0.xml')->assertOk();

    foreach ($translations as $entry) {
        $path = $entry->get('french_url').'/';
        $source = Entry::find($entry->get('translation_of'));
        if ($entry->template() === 'not-found') {
            $this->get($path)->assertNotFound();
            $sitemap->assertDontSee($path);

            continue;
        }

        $xpath = localizationXPath($this->get($path)->assertOk()->getContent());
        expect($xpath->evaluate('string(/html/@lang)'))->toBe('fr');
        expect($xpath->evaluate('string(//h1)'))->toBe($entry->get('heading') ?: ($entry->get('headline') ?: $entry->get('title')));
        expect($xpath->evaluate('string(//link[@rel="canonical"]/@href)'))->toBe('https://andrebreia.dev'.$path);
        $englishPath = rtrim($source->url(), '/').'/';
        expect($xpath->evaluate('string(//nav[@data-language-switcher]/a/@href)'))->toBe($englishPath);
        expect($xpath->evaluate('string(//link[@hreflang="en"]/@href)'))->toBe('https://andrebreia.dev'.$englishPath);
        expect($xpath->evaluate('string(//link[@hreflang="fr"]/@href)'))->toBe('https://andrebreia.dev'.$path);
        expect($xpath->evaluate('string(//link[@hreflang="x-default"]/@href)'))->toBe('https://andrebreia.dev'.$englishPath);
        $english = localizationXPath($this->get($englishPath)->assertOk()->getContent());
        expect($english->evaluate('string(//link[@hreflang="fr"]/@href)'))->toBe('https://andrebreia.dev'.$path);
        $sitemap->assertSee('https://andrebreia.dev'.$path);
        $image = $xpath->evaluate('string(//meta[@property="og:image"]/@content)');
        expect(file_exists(public_path(parse_url($image, PHP_URL_PATH))))->toBeTrue();
    }

    $this->get('/fr/')->assertSee('https://cal.com/andrebreia')->assertSee('mailto:contact@andrebreia.dev');
    $about = localizationXPath($this->get('/fr/a-propos/')->assertOk()->getContent());
    expect($about->query('//div[@id="floating-nav-wrapper"]//a[@aria-current]')->length)->toBe(1);
    expect($about->evaluate('string(//div[@id="floating-nav-wrapper"]//a[@aria-current]/@href)'))->toBe('/fr/a-propos');
    $this->get('/fr/articles/')->assertSee('3 juin 2026')->assertDontSee('The Uncomfortable Middle');
    expect(config('statamic.editions.pro'))->toBeFalse();
    expect(config('statamic.system.multisite'))->toBeFalse();
});

it('does not expose unpublished translations or invent equivalent French URLs', function () {
    $translation = Entry::query()->where('translation_of', '0dd1581a-972d-45be-99e2-e31c5d6b6e3f')->first();
    $path = $translation->get('french_url').'/';
    $translation->published(false);

    try {
        $this->get($path)->assertNotFound()->assertSee('lang="fr"', false);
        $this->get('/fr/articles/')->assertDontSee($path);
        $this->get('/fr/')->assertDontSee($path);
        $this->get('/sitemap-0.xml')->assertDontSee($path);
        $english = localizationXPath($this->get('/articles/the-uncomfortable-middle/')->assertOk()->getContent());
        expect($english->query('//link[@rel="alternate"]')->length)->toBe(0);
        expect($english->evaluate('string(//nav[@data-language-switcher]/a)'))->toBe('French homepage');
        expect($english->evaluate('string(//nav[@data-language-switcher]/a/@href)'))->toBe('/fr/');
        $this->get('/fr/articles/the-uncomfortable-middle/')->assertNotFound();
    } finally {
        $translation->published(true);
    }
});

it('keeps article counts and pagination independent between languages', function () {
    $drafts = Entry::query()->where('collection', 'articles')->get()->filter(fn ($entry) => ! $entry->published());
    foreach ($drafts as $draft) {
        $draft->published(true);
    }

    try {
        $this->get('/articles/2/')->assertOk()->assertSee('6 Raycast extensions for Laravel developers');
        $this->get('/fr/articles/')->assertOk()->assertDontSee('href="/fr/articles/2/"', false)->assertDontSee('Going full-time freelance');
        $this->get('/fr/articles/2/')->assertNotFound()->assertSee('Page introuvable');
        $this->get('/sitemap-0.xml')->assertSee('https://andrebreia.dev/articles/2/')->assertDontSee('https://andrebreia.dev/fr/articles/2/');
        $xpath = localizationXPath($this->get('/articles/2/')->getContent());
        expect($xpath->query('//link[@rel="alternate"]')->length)->toBe(0);
        expect($xpath->evaluate('string(//nav[@data-language-switcher]/a/@href)'))->toBe('/fr/articles/');
    } finally {
        foreach ($drafts as $draft) {
            $draft->published(false);
        }
    }
});

it('returns localized errors without changing language based on browser preferences', function () {
    $this->withHeader('Accept-Language', 'fr-FR')->get('/')->assertOk()->assertSee('lang="en"', false);
    $this->withHeader('Accept-Language', 'en-GB')->get('/fr/')->assertOk()->assertSee('lang="fr"', false);
    $response = $this->get('/fr/missing-page/')->assertNotFound()->assertSee('Page introuvable')->assertSee('noindex, follow');
    $xpath = localizationXPath($response->getContent());
    expect($xpath->query('//link[@rel="alternate"]')->length)->toBe(0);
    expect($xpath->evaluate('string(//main/div/a/@href)'))->toBe('/fr/');
    $this->get('/missing-page/')->assertNotFound()->assertSee('Page not found')->assertSee('lang="en"', false);
    foreach (['/not-found/', '/fr/introuvable/', '/404.html', '/fr/missing-page/'] as $path) {
        $error = localizationXPath($this->get($path)->assertNotFound()->assertSee('noindex, follow')->getContent());
        expect($error->query('//link[@rel="canonical" or @rel="alternate"]')->length)->toBe(0);
        expect($error->query('//script[@type="application/ld+json"]')->length)->toBe(0);
    }
});

it('keeps cached English and French responses separate in either request order', function (array $paths) {
    config(['statamic.static_caching.strategy' => 'half']);
    $cache = app(StaticCacheManager::class);
    $cache->flush();

    try {
        foreach (array_merge($paths, array_reverse($paths)) as $index => $path) {
            $response = $this->get($path)->assertOk();
            $response->assertSee($path === '/fr/' ? 'lang="fr"' : 'lang="en"', false);
            expect($cache->driver()->hasCachedPage(request()))->toBeTrue();
            if ($index >= 2) {
                expect($response->baseResponse->wasStaticallyCached())->toBeTrue();
            }
        }
    } finally {
        $cache->flush();
    }
})->with([
    'English first' => [['/', '/fr/']],
    'French first' => [['/fr/', '/']],
]);

it('edits French content in Core and rejects invalid or duplicate translation links', function () {
    $this->app = require base_path('bootstrap/app.php');
    $this->app->beforeBootstrapping(RegisterProviders::class, function () {
        config(['statamic.cp.enabled' => true]);
    });
    $this->app->make(Kernel::class)->bootstrap();
    $directory = sys_get_temp_dir().'/statamic-french-'.bin2hex(random_bytes(8));
    File::copyDirectory(base_path('content'), $directory);
    Stache::store('entries')->directory($directory.'/collections');
    Stache::store('collection-trees')->directory($directory.'/trees/collections');
    $this->actingAs(User::make()->id('test-editor')->email('editor@example.test')->makeSuper());

    try {
        $entry = Entry::query()->where('translation_of', 'ae607154-3cd0-4727-950e-337445236217')->first();
        $endpoint = '/cp/collections/pages/entries/'.$entry->id();
        $payload = [...$entry->blueprint()->fields()->addValues($entry->data()->all())->preProcess()->values()->all(), 'slug' => $entry->slug(), 'published' => true];
        $this->patchJson($endpoint, [...$payload, 'heading' => 'Présentation mise à jour'])
            ->assertSuccessful()->assertJsonPath('saved', true);
        expect(File::get($directory.'/collections/pages/'.$entry->slug().'.md'))->toContain('Présentation mise à jour');
        $this->get('/fr/a-propos/')->assertOk()->assertSee('Présentation mise à jour');

        foreach ([
            ['translation_of' => ['missing-source']],
            ['translation_of' => [$entry->id()]],
            ['translation_of' => ['86f03219-8161-4f20-b643-dc7d703cfaec']],
            ['translation_of' => ['ec0290bd-e4a1-4118-ab84-49c119fc3041']],
            ['french_url' => '/outside-french'],
            ['french_url' => '/fr/articles/2'],
        ] as $invalid) {
            $this->patchJson($endpoint, [...$payload, ...$invalid])->assertUnprocessable()->assertJsonValidationErrors(array_keys($invalid));
            expect(File::get($directory.'/collections/pages/'.$entry->slug().'.md'))->toContain('Présentation mise à jour');
        }
        $this->patchJson($endpoint, [...$payload, 'french_url' => '/fr/services'])
            ->assertUnprocessable()->assertJsonValidationErrors('slug');

        $drafts = Entry::query()->where('collection', 'articles')->get()->filter(fn ($article) => ! $article->published());
        foreach ($drafts->values() as $index => $draft) {
            $values = [
                ...$draft->data()->all(), 'content_language' => 'fr', 'translation_of' => $draft->id(),
                'date' => '2026-07-0'.($index + 1), 'french_url' => '/fr/articles/test-'.($index + 1),
                'title' => 'Article de test '.($index + 1),
            ];
            $fields = $draft->blueprint()->fields()->addValues($values)->preProcess()->values()->all();
            $this->postJson('/cp/collections/articles/entries/default', [
                ...$fields, '_blueprint' => 'article', 'slug' => 'fr-test-'.($index + 1), 'published' => true,
            ])->assertSuccessful()->assertJsonPath('saved', true);
        }
        $this->get('/fr/articles/')->assertSee('href="/fr/articles/2/"', false)->assertSee('Article de test 2');
        $secondPage = $this->get('/fr/articles/2/')->assertOk()->assertSee('6 extensions Raycast')->assertDontSee('Article de test 2');
        expect(localizationXPath($secondPage->getContent())->evaluate('string(//nav[@aria-label="Pagination"]//a/@href)'))->toBe('/fr/articles/');
        $this->get('/fr/articles/3/')->assertNotFound();
        $this->get('/articles/2/')->assertNotFound();
        $this->get('/sitemap-0.xml')->assertSee('https://andrebreia.dev/fr/articles/2/');
    } finally {
        File::deleteDirectory($directory);
    }
});

it('connects localized article metadata to its visible author and dates', function (string $language, string $path, string $title, string $heading, string $profile, string $updatedPath, string $dateText) {
    $xpath = localizationXPath($this->get($path.'?utm_source=newsletter')->assertOk()->getContent());
    expect($xpath->evaluate('string(//title)'))->toBe($title.' – André Breia');
    expect($xpath->evaluate('string(//h1)'))->toBe($heading);
    expect($xpath->evaluate('string(//link[@rel="canonical"]/@href)'))->toBe('https://andrebreia.dev'.$path);
    expect($xpath->evaluate('string(//a[@rel="author"]/@href)'))->toBe($profile);
    expect($xpath->evaluate('string(//meta[@name="robots"]/@content)'))->toBe('max-image-preview:large');
    expect($xpath->evaluate('string(//meta[@property="og:image:alt"]/@content)'))->toBe($heading);
    expect($xpath->evaluate('string(//meta[@property="og:locale:alternate"]/@content)'))->toBe($language === 'fr' ? 'en_GB' : 'fr_FR');
    $graph = collect(json_decode($xpath->evaluate('string(//script[@type="application/ld+json"])'), true)['@graph']);
    $article = $graph->firstWhere('@type', 'BlogPosting');
    expect($article['headline'])->toBe($heading);
    expect($article['inLanguage'])->toBe($language);
    expect($article['mainEntityOfPage']['@id'])->toBe('https://andrebreia.dev'.$path.'#webpage');
    expect($article['author']['@id'])->toBe('https://andrebreia.dev/#person');
    expect($graph->firstWhere('@type', 'Person')['url'])->toBe('https://andrebreia.dev'.$profile);
    expect($xpath->evaluate('string(//header//time/@datetime)'))->toBe('2026-06-03T00:00:00.000000Z');
    expect($article['datePublished'])->toBe('2026-06-03T00:00:00.000000Z');

    $updated = localizationXPath($this->get($updatedPath)->assertOk()->assertSee($dateText)->getContent());
    expect($updated->evaluate('string(//header//time/@datetime)'))->toBe('2026-02-07T00:00:00.000000Z');
    expect($updated->evaluate('string(//header//span/time/@datetime)'))->toBe('2026-11-16T00:00:00.000000Z');
    expect($updated->evaluate('string(//meta[@property="article:modified_time"]/@content)'))->toBe('2026-11-16T00:00:00.000000Z');

    $profilePage = localizationXPath($this->get($profile)->assertOk()->getContent());
    $profileGraph = collect(json_decode($profilePage->evaluate('string(//script[@type="application/ld+json"])'), true)['@graph']);
    expect($profileGraph->firstWhere('@type', 'ProfilePage')['mainEntity']['@id'])->toBe('https://andrebreia.dev/#person');
    expect($profileGraph->firstWhere('@type', 'ProfilePage')['inLanguage'])->toBe($language);
})->with([
    'English' => ['en', '/articles/the-uncomfortable-middle/', 'Career Identity After a Layoff', 'The Uncomfortable Middle', '/about/', '/articles/going-full-time-freelance/', 'Updated'],
    'French' => ['fr', '/fr/articles/cet-entre-deux-inconfortable/', 'L’identité professionnelle après un licenciement', 'Cet entre-deux inconfortable', '/fr/a-propos/', '/fr/articles/passer-au-freelance-a-temps-plein/', 'Mis à jour le'],
]);

it('returns 404 for pagination when its landing page is unpublished or missing', function (string $language, string $path, string $slug) {
    $directory = sys_get_temp_dir().'/statamic-pagination-'.bin2hex(random_bytes(8));
    File::copyDirectory(base_path('content'), $directory);
    Stache::store('entries')->directory($directory.'/collections');
    Stache::store('collection-trees')->directory($directory.'/trees/collections');

    try {
        $drafts = Entry::query()->where('collection', 'articles')->where('published', false)->get();
        foreach ($drafts as $index => $draft) {
            if ($language === 'fr') {
                Entry::make()->collection('articles')->slug('fr-pagination-'.$index)->date($draft->date())
                    ->data([...$draft->data()->all(), 'content_language' => 'fr', 'translation_of' => $draft->id(), 'french_url' => '/fr/articles/pagination-'.$index])
                    ->published(true)->save();
            } else {
                $draft->published(true)->save();
            }
        }
        $landing = Entry::query()->where('collection', 'pages')->where('slug', $slug)->first();
        $landing->set('heading', 'Private landing heading')->save();
        $this->get($path.'2/')->assertOk()->assertSee('Private landing heading');

        $landing->published(false)->save();
        foreach ([$path, $path.'2/'] as $url) {
            $this->get($url)->assertNotFound()->assertDontSee('Private landing heading');
        }
        $this->get('/sitemap-0.xml')->assertOk()->assertDontSee('https://andrebreia.dev'.$path.'2/');

        $landing->delete();
        $this->get($path.'2/')->assertNotFound();
    } finally {
        File::deleteDirectory($directory);
    }
})->with([
    'English' => ['en', '/articles/', 'articles'],
    'French' => ['fr', '/fr/articles/', 'fr-articles'],
]);

it('keeps French pages available when an English page slug changes', function () {
    $directory = sys_get_temp_dir().'/statamic-renamed-pages-'.bin2hex(random_bytes(8));
    File::copyDirectory(base_path('content'), $directory);
    Stache::store('entries')->directory($directory.'/collections');
    Stache::store('collection-trees')->directory($directory.'/trees/collections');

    try {
        Entry::find('ae607154-3cd0-4727-950e-337445236217')->slug('profile')->save();
        $this->get('/fr/')->assertOk();
        $article = localizationXPath($this->get('/fr/articles/cet-entre-deux-inconfortable/')->assertOk()->getContent());
        expect($article->evaluate('string(//a[@rel="author"]/@href)'))->toBe('/fr/');

        $drafts = Entry::query()->where('collection', 'articles')->where('published', false)->get();
        foreach ($drafts as $index => $draft) {
            Entry::make()->collection('articles')->slug('fr-rename-'.$index)->date($draft->date())
                ->data([...$draft->data()->all(), 'content_language' => 'fr', 'translation_of' => $draft->id(), 'french_url' => '/fr/articles/rename-'.$index])
                ->published(true)->save();
        }
        Entry::find('5857c2cb-2bfd-4e79-8cf6-f0f67375e2cf')->slug('journal')->save();
        $this->get('/fr/')->assertOk();
        $firstPage = localizationXPath($this->get('/fr/articles/')->assertOk()->getContent());
        expect($firstPage->evaluate('string(//nav[@aria-label="Pagination"]//a/@href)'))->toBe('/fr/articles/2/');
        $secondPage = localizationXPath($this->get('/fr/articles/2/')->assertOk()->assertSee('6 extensions Raycast')->getContent());
        expect($secondPage->evaluate('string(//nav[@aria-label="Pagination"]//a/@href)'))->toBe('/fr/articles/');
        expect($secondPage->evaluate('string(//link[@rel="canonical"]/@href)'))->toBe('https://andrebreia.dev/fr/articles/2/');
        $this->get('/sitemap-0.xml')->assertOk()->assertSee('https://andrebreia.dev/fr/articles/2/')->assertDontSee('https://andrebreia.dev/fr/2/');
        $this->get('/articles/2/')->assertNotFound();
    } finally {
        File::deleteDirectory($directory);
    }
});

it('caches English and French pagination responses separately', function () {
    $directory = sys_get_temp_dir().'/statamic-pagination-cache-'.bin2hex(random_bytes(8));
    File::copyDirectory(base_path('content'), $directory);
    Stache::store('entries')->directory($directory.'/collections');
    Stache::store('collection-trees')->directory($directory.'/trees/collections');
    config(['statamic.static_caching.strategy' => 'half']);
    $cache = app(StaticCacheManager::class);
    $cache->flush();

    try {
        $drafts = Entry::query()->where('collection', 'articles')->where('published', false)->get();
        foreach ($drafts as $index => $draft) {
            Entry::make()->collection('articles')->slug('fr-cache-'.$index)->date($draft->date())
                ->data([...$draft->data()->all(), 'content_language' => 'fr', 'translation_of' => $draft->id(), 'french_url' => '/fr/articles/cache-'.$index])
                ->published(true)->save();
            $draft->published(true)->save();
        }

        foreach (['/articles/2/', '/fr/articles/2/', '/fr/articles/2/', '/articles/2/'] as $index => $path) {
            $response = $this->get($path)->assertOk();
            $response->assertSee($path === '/articles/2/' ? '6 Raycast extensions' : '6 extensions Raycast');
            $response->assertSee('https://andrebreia.dev'.$path);
            expect($cache->driver()->hasCachedPage(request()))->toBeTrue();
            if ($index >= 2) {
                expect($response->baseResponse->wasStaticallyCached())->toBeTrue();
            }
        }
    } finally {
        $cache->flush();
        File::deleteDirectory($directory);
    }
});

it('returns 404 for out-of-range pagination including oversized integers', function (string $prefix) {
    foreach (['999', '999999999999999999999'] as $page) {
        $this->get($prefix.$page.'/')->assertNotFound()->assertSee('noindex, follow');
    }
})->with(['/articles/', '/fr/articles/']);

it('rejects editor changes that would break translation routing or existing pairs', function (string $id, array $changes, string $field, string $message) {
    $this->app = require base_path('bootstrap/app.php');
    $this->app->beforeBootstrapping(RegisterProviders::class, function () {
        config(['statamic.cp.enabled' => true]);
    });
    $this->app->make(Kernel::class)->bootstrap();
    $directory = sys_get_temp_dir().'/statamic-invalid-edit-'.bin2hex(random_bytes(8));
    File::copyDirectory(base_path('content'), $directory);
    Stache::store('entries')->directory($directory.'/collections');
    Stache::store('collection-trees')->directory($directory.'/trees/collections');
    $this->actingAs(User::make()->id('test-editor')->email('editor@example.test')->makeSuper());

    try {
        $entry = Entry::find($id);
        $path = $entry->path();
        $original = File::get($path);
        $values = $entry->data()->all();
        if ($entry->collection()->dated()) {
            $values['date'] = $entry->date()->toDateTimeString();
            $changes['translation_of'] = [Entry::query()->where('collection', 'articles')->where('published', false)->first()->id()];
        }
        $values = $entry->blueprint()->fields()->addValues($values)->preProcess()->values()->all();

        $this->patchJson('/cp/collections/'.$entry->collectionHandle().'/entries/'.$id, [
            ...$values, 'slug' => $entry->slug(), 'published' => true, ...$changes,
        ])->assertUnprocessable()->assertJsonValidationErrors([$field => $message]);
        expect(File::get($path))->toBe($original);
    } finally {
        File::deleteDirectory($directory);
    }
})->with([
    'French listing path' => ['90a1d700-73c6-474e-a8a2-b879293dd136', ['french_url' => '/fr/journal'], 'french_url', 'The French articles listing must use /fr/articles so pagination remains available.'],
    'English source language' => ['0dd1581a-972d-45be-99e2-e31c5d6b6e3f', ['content_language' => 'fr', 'french_url' => '/fr/articles/reclassified'], 'content_language', 'Keep this entry in English while French translations reference it.'],
]);

it('omits invalid translation counterparts from rendered language links', function (string $sourceId) {
    $translation = Entry::query()->where('translation_of', '0dd1581a-972d-45be-99e2-e31c5d6b6e3f')->first();
    $original = $translation->get('translation_of');
    $translation->set('translation_of', $sourceId);

    try {
        $xpath = localizationXPath($this->get('/fr/articles/cet-entre-deux-inconfortable/')->assertOk()->getContent());
        expect($xpath->query('//link[@rel="alternate"]')->length)->toBe(0);
        expect($xpath->evaluate('string(//nav[@data-language-switcher]/a/@href)'))->toBe('/');
        expect($xpath->evaluate('string(//nav[@data-language-switcher]/a)'))->toBe('Accueil en anglais');
    } finally {
        $translation->set('translation_of', $original);
    }
})->with([
    'wrong language' => ['174e18f0-87dc-4ec5-ab22-bc0496087512'],
    'wrong collection' => ['ae607154-3cd0-4727-950e-337445236217'],
]);
