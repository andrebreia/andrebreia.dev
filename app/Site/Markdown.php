<?php

namespace App\Site;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use DOMXPath;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;

class Markdown
{
    /** Convert the site's rendered main content, keeping CMS content as the source of truth. */
    public static function render(string $html, string $canonical): string
    {
        $document = new DOMDocument;
        @$document->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NONET);
        $main = (new DOMXPath($document))->query('//main')->item(0);
        $content = $main ? self::node($main, $canonical) : '';

        return 'Source: '.$canonical."\n\n".trim($content)."\n";
    }

    private static function node(DOMNode $node, string $canonical): string
    {
        if ($node instanceof DOMText) {
            return self::escape(preg_replace('/\s+/u', ' ', $node->textContent));
        }
        if (! $node instanceof DOMElement) {
            return '';
        }
        $tag = $node->tagName;
        if (in_array($tag, ['script', 'style', 'svg', 'footer'])
            || $node->hasAttribute('data-language-switcher')
            || $node->getAttribute('aria-hidden') === 'true') {
            return '';
        }
        if (in_array($tag, ['pre', 'code'])) {
            $code = $node->textContent;
            preg_match_all('/`+/', $code, $matches);
            $fence = str_repeat('`', max([$tag === 'pre' ? 3 : 1, ...array_map(fn ($match) => strlen($match) + 1, $matches[0])]));

            if ($tag === 'pre') {
                return "\n\n".$fence."\n".$code.(str_ends_with($code, "\n") ? '' : "\n").$fence."\n\n";
            }

            $code = str_replace("\n", ' ', $code);
            $padding = trim($code, ' ') === '' ? '' : ' ';

            return $fence.$padding.$code.$padding.$fence;
        }
        if (in_array($tag, ['ul', 'ol'])) {
            $items = [];
            $number = (int) ($node->getAttribute('start') ?: 1);
            foreach ($node->childNodes as $child) {
                if ($child instanceof DOMElement && $child->tagName === 'li') {
                    $prefix = $tag === 'ol' ? $number++.'. ' : '- ';
                    $items[] = $prefix.str_replace("\n", "\n".str_repeat(' ', strlen($prefix)), trim(self::children($child, $canonical)));
                }
            }

            return "\n\n".implode("\n", $items)."\n\n";
        }
        if ($tag === 'table') {
            $rows = [];
            foreach ($node->getElementsByTagName('tr') as $row) {
                $cells = [];
                foreach ($row->childNodes as $cell) {
                    if ($cell instanceof DOMElement && in_array($cell->tagName, ['th', 'td'])) {
                        $cells[] = preg_replace('/\n+/', ' ', trim(self::children($cell, $canonical)));
                    }
                }
                $rows[] = '| '.implode(' | ', $cells).' |';
                if (count($rows) === 1) {
                    $rows[] = '| '.implode(' | ', array_fill(0, count($cells), '---')).' |';
                }
            }

            return "\n\n".implode("\n", $rows)."\n\n";
        }

        $content = self::children($node, $canonical);

        return match ($tag) {
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6' => "\n\n".str_repeat('#', (int) substr($tag, 1)).' '.trim($content)."\n\n",
            'p', 'div', 'section', 'article', 'header', 'nav', 'figure', 'figcaption' => trim($content) === '' ? '' : "\n\n".trim($content)."\n\n",
            'strong', 'b' => self::inline($content, '**'),
            'em', 'i' => self::inline($content, '*'),
            's', 'del' => self::inline($content, '~~'),
            'br' => '<br>',
            'hr' => "\n\n---\n\n",
            'blockquote' => "\n\n> ".str_replace("\n", "\n> ", trim($content))."\n\n",
            'a' => self::link($node, $content, $canonical),
            'img' => $node->getAttribute('alt') === '' ? '' : '!['.self::escape($node->getAttribute('alt')).'](<'.self::url($node->getAttribute('src'), $canonical).'>)',
            default => $content,
        };
    }

    private static function children(DOMNode $node, string $canonical): string
    {
        $content = '';
        foreach ($node->childNodes as $child) {
            $content .= self::node($child, $canonical);
        }

        return $content;
    }

    private static function link(DOMElement $node, string $content, string $canonical): string
    {
        $label = trim(preg_replace('/\n+/', ' ', $content)) ?: self::escape($node->getAttribute('aria-label'));
        $href = $node->getAttribute('href');

        if ($href !== '' && preg_match('/(?:^|\n)(#{1,6}) ([^\n]+)/', $content, $heading)) {
            return "\n\n".str_replace($heading[1].' '.$heading[2], $heading[1].' ['.$heading[2].'](<'.self::url($href, $canonical).'>)', trim($content))."\n\n";
        }

        return $href === '' || $label === '' ? $label : '['.$label.'](<'.self::url($href, $canonical).'>)';
    }

    private static function inline(string $content, string $marker): string
    {
        return preg_replace('/^(\s*)(.*?)(\s*)$/s', '$1'.$marker.'$2'.$marker.'$3', $content);
    }

    public static function escape(string $text): string
    {
        return str_replace(['\\', '*', '_', '[', ']', '`', '|', '#', '<', '>'], ['\\\\', '\\*', '\\_', '\\[', '\\]', '\\`', '\\|', '\\#', '&lt;', '&gt;'], $text);
    }

    private static function url(string $url, string $canonical): string
    {
        return str_replace(['<', '>'], ['%3C', '%3E'], (string) UriResolver::resolve(new Uri($canonical), new Uri($url)));
    }
}
