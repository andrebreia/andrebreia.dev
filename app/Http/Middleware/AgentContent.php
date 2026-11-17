<?php

namespace App\Http\Middleware;

use App\Site\Markdown;
use App\Site\SiteData;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\Response;

class AgentContent
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethodSafe()
            || ! $request->routeIs('statamic.site', 'articles.*', 'agent.markdown*')
            || ! $response->isOk()
            || ! str_starts_with($response->headers->get('Content-Type', ''), 'text/html')
            || $response->headers->has('X-Statamic-Draft')
            || $response->headers->has('X-Statamic-Private')
            || $response->headers->has('X-Statamic-Protected')) {
            return $response;
        }

        $base = rtrim(SiteData::globals('site')['url'], '/');
        $path = '/'.trim($request->path(), '/');
        $path = $request->routeIs('agent.markdown*') ? substr($path, 0, -strlen('index.md')) : rtrim($path, '/').'/';
        $canonical = $base.$path;
        $response->setVary('Accept', false);
        $response->headers->set('Link', implode(', ', [
            '<'.$canonical.'>; rel="canonical"',
            '<'.$canonical.'index.md>; rel="alternate"; type="text/markdown"',
            '<'.$base.'/llms.txt>; rel="describedby"; type="text/plain"',
            '<'.$base.'/.well-known/agent-skills/index.json>; rel="describedby"; type="application/json"',
            '<'.$base.'/.well-known/ai-catalog.json>; rel="describedby"; type="application/json"',
            '<'.$base.'/.well-known/ard.json>; rel="ard"; type="application/json"',
        ]), false);

        $accept = AcceptHeader::fromString($request->header('Accept', ''));
        $wantsMarkdown = ($accept->get('text/markdown; charset=UTF-8')?->getQuality() ?? 0)
            > ($accept->get($response->headers->get('Content-Type'))?->getQuality() ?? 0);

        if ($request->routeIs('agent.markdown*') || $wantsMarkdown) {
            if (! $request->isMethod('HEAD')) {
                $response->setContent(Markdown::render($response->getContent(), $canonical));
            }
            $response->headers->set('Content-Type', 'text/markdown; charset=UTF-8');
            $response->headers->set('Content-Location', $canonical.'index.md');
            $response->headers->remove('Content-Length');
            $response->headers->remove('ETag');
        }

        return $response;
    }
}
