<?php

use App\Site\Markdown;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Statamic\Facades\Entry;

it('negotiates Markdown without changing the HTML default or ignoring quality weights', function (string $accept, string $type) {
    $response = $this->get('/', ['Accept' => $accept]);

    $response->assertOk()->assertHeader('Content-Type', $type.'; charset=UTF-8')
        ->assertHeaderContains('Vary', 'Accept')
        ->assertHeaderContains('Link', '<https://andrebreia.dev/index.md>; rel="alternate"; type="text/markdown"')
        ->assertHeaderContains('Link', '<https://andrebreia.dev/llms.txt>; rel="describedby"');
})->with([
    'no preference' => ['', 'text/html'],
    'wildcard' => ['*/*', 'text/html'],
    'browser' => ['text/html,application/xhtml+xml,*/*;q=0.8', 'text/html'],
    'Markdown' => ['text/markdown', 'text/markdown'],
    'prefers Markdown' => ['text/html;q=0.3,text/markdown;q=0.9', 'text/markdown'],
    'prefers HTML' => ['text/markdown;q=0.3,text/html;q=0.9', 'text/html'],
    'rejects Markdown' => ['text/markdown;q=0', 'text/html'],
    'Markdown with charset' => ['text/markdown;charset=UTF-8', 'text/markdown'],
    'specific preferences override wildcard' => ['*/*;q=1,text/html;q=0.1,text/markdown;q=0.9', 'text/markdown'],
    'charset preference overrides generic type' => ['text/markdown;charset=utf-8;q=0.2,text/markdown;q=1,text/html;q=0.8', 'text/html'],
    'unsupported charset' => ['text/markdown;charset=iso-8859-1', 'text/html'],
    'tie prefers HTML' => ['text/markdown;q=0.8,text/html;q=0.8', 'text/html'],
    'wildcard does not override rejection' => ['text/markdown;q=0,text/html;q=0.1,*/*;q=1', 'text/html'],
]);

it('returns Markdown discovery headers without a body for HEAD requests', function (string $path) {
    $this->head($path, ['Accept' => 'text/markdown'])->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')
        ->assertHeaderContains('Link', 'rel="alternate"')
        ->assertContent('');
})->with(['/', '/index.md']);

it('serves the same published content through Markdown URLs in both languages', function () {
    $index = $this->get('/llms.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee(['## English', '## Français', 'https://andrebreia.dev/fr/a-propos/index.md'], false)
        ->assertDontSee(['how-i-use-ai-coding-agents', 'lessons-from-7-years-as-a-technical-lead', '404.html']);
    preg_match_all('#\]\(https://andrebreia.dev([^)]*/index.md)\)#', $index->getContent(), $matches);
    expect($matches[1])->not->toBeEmpty();

    foreach ($matches[1] as $path) {
        $canonicalPath = substr($path, 0, -strlen('index.md'));
        $markdown = $this->get($path)->assertOk()->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')
            ->assertSee('Source: https://andrebreia.dev'.$canonicalPath, false)
            ->assertDontSee(['<html', '<script', '<svg', 'data-language-switcher'], false);
        $negotiated = $this->get($canonicalPath, ['Accept' => 'text/markdown'])->assertOk();
        expect($markdown->getContent())->toBe($negotiated->getContent());
    }
});

it('keeps Markdown content meaningful with headings links tables lists and code from the CMS', function () {
    $article = Entry::query()->where('slug', 'the-uncomfortable-middle')->first();
    $body = $article->get('body');
    $paragraph = fn (string $text): array => ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => $text]]];
    $article->set('body', [
        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Résumé & deployment']]],
        ['type' => 'paragraph', 'content' => [
            ['type' => 'text', 'text' => 'Read '],
            ['type' => 'text', 'text' => 'the service', 'marks' => [['type' => 'link', 'attrs' => ['href' => '/services/api-integrations/']]]],
            ['type' => 'text', 'text' => ' with care.', 'marks' => [['type' => 'bold']]],
        ]],
        ['type' => 'orderedList', 'content' => [
            ['type' => 'listItem', 'content' => [$paragraph('Run tests')]],
            ['type' => 'listItem', 'content' => [$paragraph('Deploy')]],
        ]],
        ['type' => 'table', 'content' => [
            ['type' => 'tableRow', 'content' => [
                ['type' => 'tableHeader', 'content' => [$paragraph('Task')]],
                ['type' => 'tableHeader', 'content' => [$paragraph('Result')]],
            ]],
            ['type' => 'tableRow', 'content' => [
                ['type' => 'tableCell', 'content' => [$paragraph('Tests')]],
                ['type' => 'tableCell', 'content' => [$paragraph('Pass | fail')]],
            ]],
        ]],
        ['type' => 'codeBlock', 'content' => [['type' => 'text', 'text' => "echo '<safe>';\n    run();"]]],
    ]);

    try {
        $this->get('/articles/the-uncomfortable-middle/index.md')
            ->assertOk()->assertSee([
                '# The Uncomfortable Middle', '## Résumé & deployment',
                '[the service](<https://andrebreia.dev/services/api-integrations/>)', '**with care.**',
                "1. Run tests\n2. Deploy", "| Task | Result |\n| --- | --- |\n| Tests | Pass \\| fail |",
                "```\necho '<safe>';\n    run();\n```",
            ], false);
    } finally {
        $article->set('body', $body);
    }
});

it('preserves literal code when agents parse the Markdown response', function (array $node, string $selector, string $literal) {
    $article = Entry::query()->where('slug', 'the-uncomfortable-middle')->first();
    $body = $article->get('body');
    $article->set('body', [$node]);

    try {
        $response = $this->get('/articles/the-uncomfortable-middle/index.md')->assertOk();
        $document = new DOMDocument;
        @$document->loadHTML((string) (new CommonMarkConverter)->convert($response->getContent()));
        $code = (new DOMXPath($document))->query($selector);

        expect($code->length)->toBe(1);
        expect($code->item(0)->textContent)->toBe($literal);
    } finally {
        $article->set('body', $body);
    }
})->with([
    'blank lines and trailing whitespace in a block' => [
        ['type' => 'codeBlock', 'content' => [['type' => 'text', 'text' => "payload = \"\"\"first\n\n\nlast\"\"\"\n  \nend  \n\n"]]],
        '//pre/code', "payload = \"\"\"first\n\n\nlast\"\"\"\n  \nend  \n\n",
    ],
    'embedded backticks in a block' => [
        ['type' => 'codeBlock', 'content' => [['type' => 'text', 'text' => "```\nexample\n````"]]],
        '//pre/code', "```\nexample\n````\n",
    ],
    'embedded and boundary backticks in a span' => [
        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => '`one `` two```', 'marks' => [['type' => 'code']]]]],
        '//p/code', '`one `` two```',
    ],
    'all-space span' => [
        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => '   ', 'marks' => [['type' => 'code']]]]],
        '//p/code', '   ',
    ],
]);

it('preserves explicit CMS line breaks when agents parse Markdown', function () {
    $article = Entry::query()->where('slug', 'the-uncomfortable-middle')->first();
    $body = $article->get('body');
    $article->set('body', [['type' => 'paragraph', 'content' => [
        ['type' => 'text', 'text' => 'First line'],
        ['type' => 'hardBreak'],
        ['type' => 'text', 'text' => 'Second line'],
    ]]]);

    try {
        $response = $this->get('/articles/the-uncomfortable-middle/index.md')->assertOk();
        $document = new DOMDocument;
        @$document->loadHTML((string) (new CommonMarkConverter)->convert($response->getContent()));

        expect((new DOMXPath($document))->query('//p[br and contains(., "First line") and contains(., "Second line")]')->length)->toBe(1);
    } finally {
        $article->set('body', $body);
    }
});

it('preserves breaks and code spacing in Markdown containers', function (string $html, string $selector, string $text, int $breaks) {
    $markdown = Markdown::render('<main>'.$html.'</main>', 'https://andrebreia.dev/about/');
    $document = new DOMDocument;
    @$document->loadHTML((string) (new GithubFlavoredMarkdownConverter)->convert($markdown));
    $xpath = new DOMXPath($document);
    $elements = $xpath->query($selector);

    expect($elements->length)->toBe(1);
    expect(str_replace("\n", '', $elements->item(0)->textContent))->toBe($text);
    expect($xpath->query('descendant-or-self::br', $elements->item(0))->length)->toBe($breaks);
})->with([
    'terminal break' => ['<p>First line<br></p>', '//p[br]', 'First line', 1],
    'empty paragraph break' => ['<p><br></p>', '//br', '', 1],
    'consecutive breaks' => ['<p>First<br><br>Second</p>', '//p[br]', 'FirstSecond', 2],
    'linked break' => ['<p><a href="/">First<br>Second</a></p>', '//a[@href="https://andrebreia.dev/"]', 'FirstSecond', 1],
    'table break' => ['<table><tr><th>Label</th></tr><tr><td>First<br>Second</td></tr></table>', '//td', 'FirstSecond', 1],
    'linked code whitespace' => ['<p><a href="/about/"><code>a  b</code></a></p>', '//a/code', 'a  b', 0],
    'table code whitespace' => ['<table><tr><th>Code</th></tr><tr><td><p><code>a  b</code></p></td></tr></table>', '//td/code', 'a  b', 0],
    'linked prose whitespace' => ["<p><a href=\"/\">a  \n b</a></p>", '//a[@href="https://andrebreia.dev/"]', 'a b', 0],
    'table prose whitespace' => ["<table><tr><th>Label</th></tr><tr><td><p>a  \n b</p></td></tr></table>", '//td', 'a b', 0],
]);

it('does not expose drafts missing pages or non-content routes as Markdown', function (string $path) {
    $this->get($path.'/index.md', ['Accept' => 'text/markdown'])->assertNotFound();
})->with(['/articles/how-i-use-ai-coding-agents', '/missing-page', '/404.html', '/cp', '/up', '/resume.pdf', '/articles/2']);

it('keeps protected entries out of discovery and enforces protection on Markdown URLs', function () {
    $article = Entry::query()->where('slug', 'the-uncomfortable-middle')->first();
    $original = $article->get('protect');
    $article->set('protect', 'unavailable-scheme');

    try {
        $this->get('/llms.txt')->assertDontSee('the-uncomfortable-middle');
        $this->get('/articles/the-uncomfortable-middle/index.md')->assertForbidden();
    } finally {
        $article->set('protect', $original);
    }
});

it('preserves article pagination and its boundaries in Markdown', function () {
    $drafts = Entry::query()->where('collection', 'articles')->get()->filter(fn ($entry) => ! $entry->published());
    foreach ($drafts as $draft) {
        $draft->published(true);
    }

    try {
        $this->get('/articles/2/index.md')->assertOk()
            ->assertSee(['Source: https://andrebreia.dev/articles/2/', '6 Raycast extensions for Laravel developers'], false)
            ->assertDontSee('The Uncomfortable Middle');
        $this->get('/articles/2/', ['Accept' => 'text/markdown'])->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
        $this->get('/articles/3/index.md')->assertNotFound();
    } finally {
        foreach ($drafts as $draft) {
            $draft->published(false);
        }
    }
});

it('keeps cached HTML separate from negotiated Markdown in either request order', function (array $types) {
    config(['statamic.static_caching.strategy' => 'half']);

    foreach ($types as $type) {
        $response = $this->get('/about/', ['Accept' => $type])->assertOk()->assertHeader('Content-Type', $type.'; charset=UTF-8');
        if ($type === 'text/html') {
            $response->assertSee('<html', false)->assertDontSee('Source: https://andrebreia.dev/about/');
        } else {
            $response->assertSee('Source: https://andrebreia.dev/about/', false)->assertDontSee('<html', false);
        }
    }
})->with([
    'Markdown first' => [['text/markdown', 'text/html', 'text/markdown']],
    'HTML first' => [['text/html', 'text/markdown', 'text/html']],
]);

it('does not retain an alternate cached copy after content is unpublished', function () {
    config(['statamic.static_caching.strategy' => 'half']);
    $article = Entry::query()->where('slug', 'the-uncomfortable-middle')->first();
    $this->get('/articles/the-uncomfortable-middle/index.md')->assertOk();
    $article->published(false);

    try {
        $this->get('/articles/the-uncomfortable-middle/index.md')->assertNotFound();
        $this->get('/llms.txt')->assertDontSee('the-uncomfortable-middle');
    } finally {
        $article->published(true);
    }
});

it('publishes a verifiable skill and a catalog pointing only to implemented capabilities', function () {
    $index = $this->get('/.well-known/agent-skills/index.json')->assertOk()
        ->assertHeader('Content-Type', 'application/json')
        ->assertJsonPath('$schema', 'https://schemas.agentskills.io/discovery/0.2.0/schema.json')
        ->assertJsonPath('skills.0.name', 'reading-andre-breia')
        ->assertJsonPath('skills.0.type', 'skill-md');
    $skill = $this->get($index->json('skills.0.url'))->assertOk()->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')
        ->assertSee('https://andrebreia.dev/llms.txt', false);
    expect($index->json('skills.0.digest'))->toBe('sha256:'.hash('sha256', $skill->getContent()));

    $catalog = $this->get('/.well-known/ai-catalog.json')->assertOk()->assertHeader('Access-Control-Allow-Origin', '*')
        ->assertJsonPath('specVersion', '1.0')
        ->assertJsonPath('entries.0.type', 'text/markdown')
        ->assertJsonPath('entries.0.url', 'https://andrebreia.dev'.$index->json('skills.0.url'))
        ->assertJsonMissingPath('entries.0.data');

    $this->get('/.well-known/ard.json')->assertOk()->assertHeader('Content-Type', 'application/json')
        ->assertHeader('Access-Control-Allow-Origin', '*')->assertExactJson($catalog->json());
    $page = $this->get('/')->assertHeaderContains('Link', '<https://andrebreia.dev/.well-known/ard.json>; rel="ard"; type="application/json"');
    $document = new DOMDocument;
    @$document->loadHTML($page->getContent());
    $links = (new DOMXPath($document))->query('//head/link[@rel="ard"]');

    expect($links->length)->toBe(1);
    expect($links->item(0)->getAttribute('type'))->toBe('application/json');
    $this->get($links->item(0)->getAttribute('href'))->assertOk()->assertExactJson($catalog->json());
});
