<?php

namespace App\Listeners;

use App\Site\SiteData;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Statamic\Events\EntrySaving;
use Statamic\Facades\Entry;

class ValidateTranslation
{
    public function handle(EntrySaving $event): void
    {
        $entry = $event->entry;
        $language = SiteData::language($entry);
        Validator::make(['content_language' => $language], ['content_language' => 'required|in:en,fr'])->validate();

        if ($language !== 'fr') {
            return;
        }

        if ($entry->id() && Entry::query()->where('translation_of', $entry->id())->exists()) {
            throw ValidationException::withMessages(['content_language' => 'Keep this entry in English while French translations reference it.']);
        }

        Validator::make($entry->data()->all(), [
            'translation_of' => ['required', 'string'],
            'french_url' => ['required', 'string', 'regex:~^/fr(?:/[a-z0-9]+(?:-[a-z0-9]+)*)*$~', 'not_regex:~^/fr/articles/[0-9]+$~'],
        ])->validate();

        $source = Entry::find($entry->get('translation_of'));
        if (! $source || SiteData::language($source) !== 'en' || $source->collectionHandle() !== $entry->collectionHandle()) {
            throw ValidationException::withMessages(['translation_of' => 'Choose an English entry from the same collection.']);
        }

        if ($source->collectionHandle() === 'pages' && $source->template() === 'articles' && $entry->get('french_url') !== '/fr/articles') {
            throw ValidationException::withMessages(['french_url' => 'The French articles listing must use /fr/articles so pagination remains available.']);
        }

        if (Entry::query()->where('translation_of', $source->id())->where('id', '!=', $entry->id())->exists()) {
            throw ValidationException::withMessages(['translation_of' => 'This entry already has a French translation.']);
        }

        if (Entry::query()->where('french_url', $entry->get('french_url'))->where('id', '!=', $entry->id())->exists()) {
            throw ValidationException::withMessages(['french_url' => 'This French URL is already in use.']);
        }
    }
}
