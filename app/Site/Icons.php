<?php

namespace App\Site;

class Icons
{
    /** @var array<string, array{width: int, height: int, body: string}|null> */
    private array $icons = [];

    /** @return array{width: int, height: int, body: string}|null */
    public function get(string $name): ?array
    {
        if (! array_key_exists($name, $this->icons)) {
            $path = public_path('build/icons/'.hash('sha256', $name).'.json');
            $this->icons[$name] = is_file($path)
                ? json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR)
                : null;
        }

        return $this->icons[$name];
    }
}
