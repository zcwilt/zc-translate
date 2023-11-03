<?php

namespace App\Console\Commands\Library;

class ShortToLongLanguage
{
    const ENGLISH = 'english';
    public static function getLongLanguage(string $shortLanguage): ?string
    {
        if ($shortLanguage === 'en') {
            return self::ENGLISH;
        }
        return config('translations.' . $shortLanguage . '.longName');
    }
}
