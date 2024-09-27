<?php

namespace App\Tags;

use Spatie\ShikiPhp\Shiki as SpatieShiki;
use Statamic\Tags\Tags;

class Shiki extends Tags
{
    /**
     * {{ shiki language="php" }}{{ my_code }}{{ /shiki }}
     */
    public function index()
    {
        $language = $this->params->get('language');
        $code = $this->context->raw('code_snippet')['code'];

        return SpatieShiki::highlight(
            code: $code,
            language: $language,
            theme: 'catppuccin-frappe',
        );
    }
}
