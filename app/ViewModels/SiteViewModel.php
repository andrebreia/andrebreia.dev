<?php

namespace App\ViewModels;

use App\Site\SiteData;
use Statamic\View\ViewModel;

class SiteViewModel extends ViewModel
{
    public function data(): array
    {
        abort_if($this->cascade->content()->template() === 'not-found', 404);

        return SiteData::forEntry(
            $this->cascade->content(),
            (int) request()->route('pageNumber', 1),
        );
    }
}
