<?php

namespace App\Http\Controllers;

use App\Site\Markdown;
use App\Site\SiteData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Statamic\Auth\Protect\Protection;
use Statamic\Facades\Entry;
use Statamic\Facades\YAML;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AgentContentController extends Controller
{
    public function show(Request $request, ?string $path = null): SymfonyResponse
    {
        $uri = '/'.trim($path ?? '', '/');
        if (preg_match('#^(/(?:fr/)?articles)/([2-9]|[1-9][0-9]+)$#', $uri, $matches)) {
            $uri = $matches[1];
            $request->route()->setParameter('pageNumber', (int) $matches[2]);
        }
        $entry = Entry::findByUri($uri);
        abort_unless($entry && $entry->published() && ! $entry->private(), 404);

        return $entry->toResponse($request);
    }

    public function index(): Response
    {
        $site = SiteData::globals('site');
        $base = rtrim($site['url'], '/');
        $lines = [
            '# '.Markdown::escape($site['name']),
            '',
            '> '.Markdown::escape($site['description']),
            '',
            'Public portfolio, services, projects, and articles in English and French. No sign-in is required to read them.',
            'Fetch a page with Accept: text/markdown, or use its index.md URL below. Cite the canonical HTML URL given in the Source line.',
        ];
        $entries = Entry::query()->orderBy('title')->get()
            ->filter(fn ($entry) => $entry->published() && $entry->url() && ! $entry->private() && $entry->template() !== 'not-found'
                && ! app(Protection::class)->setData($entry)->scheme());

        foreach (['en' => 'English', 'fr' => 'Français'] as $language => $label) {
            $lines[] = "\n## ".$label."\n";
            foreach ($entries->filter(fn ($entry) => SiteData::language($entry) === $language) as $entry) {
                $description = $entry->get('description') ?: $entry->get('excerpt', '');
                $lines[] = '- ['.Markdown::escape($entry->get('title')).']('.$base.SiteData::url($entry).'index.md)'
                    .($description ? ': '.Markdown::escape(preg_replace('/\s+/u', ' ', $description)) : '');
            }
        }

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function skill(): Response
    {
        return response(file_get_contents(resource_path('agent-skills/reading-andre-breia/SKILL.md')), 200, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    public function skills(): JsonResponse
    {
        $skill = file_get_contents(resource_path('agent-skills/reading-andre-breia/SKILL.md'));
        $metadata = YAML::parse(explode('---', $skill, 3)[1]);

        return response()->json([
            '$schema' => 'https://schemas.agentskills.io/discovery/0.2.0/schema.json',
            'skills' => [[
                'name' => $metadata['name'],
                'type' => 'skill-md',
                'description' => $metadata['description'],
                'url' => route('agent.skill', absolute: false),
                'digest' => 'sha256:'.hash('sha256', $skill),
            ]],
        ])->header('Access-Control-Allow-Origin', '*');
    }

    public function catalog(): JsonResponse
    {
        $site = SiteData::globals('site');
        $base = rtrim($site['url'], '/');
        $domain = parse_url($base, PHP_URL_HOST);

        return response()->json([
            'specVersion' => '1.0',
            'host' => ['displayName' => $site['name'], 'identifier' => 'did:web:'.$domain],
            'entries' => [[
                'identifier' => 'urn:air:'.$domain.':skill:reading-andre-breia',
                'displayName' => 'Read André Breia’s portfolio and articles',
                'type' => 'text/markdown',
                'url' => $base.route('agent.skill', absolute: false),
                'representativeQueries' => [
                    'What Laravel development services does André Breia offer?',
                    'Where can I read André Breia’s articles in French?',
                    'How can I contact André Breia about a project?',
                ],
            ]],
        ])->header('Access-Control-Allow-Origin', '*');
    }
}
