<?php

namespace App\Site;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Str;

class RichText
{
    /** Preserve Markdown heading links and table styling in Bard's HTML. */
    public static function render(string $html): string
    {
        if ($html === '') {
            return '';
        }
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8" ?><div id="bard-body">'.$html.'</div>');
        $xpath = new DOMXPath($dom);
        // Bard requires paragraph blocks inside lists and cells. Unwrap only a
        // sole paragraph on the frontend to retain the original tight Markdown layout.
        foreach ($xpath->query('//li[count(*)=1]/p | //th[count(*)=1]/p | //td[count(*)=1]/p') as $paragraph) {
            $parent = $paragraph->parentNode;
            while ($paragraph->firstChild) {
                $parent->insertBefore($paragraph->firstChild, $paragraph);
            }
            $parent->removeChild($paragraph);
        }
        $ids = [];
        foreach ($xpath->query('//h2|//h3') as $heading) {
            $slug = Str::slug($heading->textContent);
            $id = $slug;
            for ($suffix = 1; isset($ids[$id]); $suffix++) {
                $id = $slug.'-'.$suffix;
            }
            $ids[$id] = true;
            $heading->setAttribute('id', $id);
        }
        foreach ($xpath->query('//table/tbody/tr[1][th]') as $row) {
            $table = $row->parentNode->parentNode;
            $head = $dom->createElement('thead');
            $table->insertBefore($head, $table->firstChild);
            $head->appendChild($row);
        }
        $output = '';
        foreach ($dom->getElementById('bard-body')->childNodes as $node) {
            $output .= $dom->saveHTML($node);
        }

        return $output;
    }
}
