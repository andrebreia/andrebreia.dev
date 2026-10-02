<?php

use App\Site\Icons;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use Illuminate\Support\Facades\File;
use Illuminate\View\ComponentAttributeBag;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;
use Statamic\Facades\User;
use Symfony\Component\Process\Process;

it('renders installed icons and ignores unknown or path-like names', function () {
    $icon = app(Icons::class)->get('lucide:check');

    expect($icon['width'])->toBe(24);
    expect($icon['body'])->toContain('<path');
    expect(view('components.icon', ['name' => 'lucide:check', 'attributes' => new ComponentAttributeBag])->render())
        ->toContain('viewBox="0 0 24 24"', $icon['body']);
    expect(app(Icons::class)->get('missing:icon'))->toBeNull();
    expect(app(Icons::class)->get('../../composer'))->toBeNull();
});

it('orders same-date articles by slug across the pagination boundary', function () {
    $articles = Entry::query()->where('collection', 'articles')->get();
    $originals = $articles->map(fn ($entry) => [$entry->date(), $entry->published()]);

    try {
        foreach ($articles as $article) {
            $article->date('2026-01-01')->published(true);
        }

        $this->get('/articles/')->assertSeeInOrder([
            '6 Raycast extensions for Laravel developers', 'Building a SaaS MVP with Laravel',
            'Going full-time freelance', 'How I use AI coding agents',
        ])->assertDontSee('The Uncomfortable Middle');
        $this->get('/articles/2/')->assertSeeInOrder([
            'Lessons from 7 years as a Technical Lead', 'The Uncomfortable Middle',
        ])->assertDontSee('Building a SaaS MVP with Laravel');
    } finally {
        foreach ($articles as $index => $article) {
            $article->date($originals[$index][0])->published($originals[$index][1]);
        }
    }
});

it('validates and persists a CP page that renders through its public URL', function () {
    $this->app = require base_path('bootstrap/app.php');
    $this->app->beforeBootstrapping(RegisterProviders::class, function () {
        config(['statamic.cp.enabled' => true]);
    });
    $this->app->make(Kernel::class)->bootstrap();
    $directory = sys_get_temp_dir().'/statamic-pages-'.bin2hex(random_bytes(8));
    File::copyDirectory(base_path('content'), $directory);
    Stache::store('entries')->directory($directory.'/collections');
    Stache::store('collection-trees')->directory($directory.'/trees/collections');
    $this->actingAs(User::make()->id('test-editor')->email('editor@example.test')->makeSuper());

    try {
        $originalCount = Entry::query()->where('collection', 'pages')->count();
        $this->postJson('/cp/collections/pages/entries/default', ['_blueprint' => 'page'])
            ->assertUnprocessable()->assertJsonValidationErrors(['title', 'heading']);
        expect(Entry::query()->where('collection', 'pages')->count())->toBe($originalCount);

        $this->postJson('/cp/collections/pages/entries/default', [
            '_blueprint' => 'page', 'slug' => 'saved-page', 'title' => 'Saved page title',
            'heading' => 'Saved page heading', 'template' => 'page', 'published' => true,
            'body' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Saved Bard body.']]]],
        ])->assertSuccessful()->assertJsonPath('saved', true);

        expect(File::get($directory.'/collections/pages/saved-page.md'))
            ->toContain('Saved page heading', 'Saved Bard body.');
        $this->get('/saved-page/')->assertOk()->assertSee('Saved page heading')->assertSee('Saved Bard body.');
    } finally {
        File::deleteDirectory($directory);
    }
});

it('disables CP routes in a real production bootstrap even when CP_ENABLED is true', function () {
    $process = new Process([PHP_BINARY, '-r', <<<'PHP'
        require 'vendor/autoload.php';
        $app = require 'bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
        $statuses = [];
        foreach (['/cp', '/cp/auth/login'] as $url) {
            $response = $kernel->handle(Illuminate\Http\Request::create($url));
            $statuses[] = $response->getStatusCode();
        }
        echo json_encode(['environment' => $app->environment(), 'statuses' => $statuses]);
        PHP,
    ], base_path(), [
        'APP_ENV' => 'production', 'CP_ENABLED' => 'true', 'CACHE_STORE' => 'array',
        'SESSION_DRIVER' => 'array', 'STATAMIC_STATIC_CACHING_STRATEGY' => 'null',
    ]);
    $process->mustRun();

    expect(json_decode($process->getOutput(), true))->toBe([
        'environment' => 'production', 'statuses' => [404, 404],
    ]);
});
