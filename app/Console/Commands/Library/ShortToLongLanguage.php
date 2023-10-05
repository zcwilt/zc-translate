<?php

namespace App\Console\Commands\Library;

class ShortToLongLanguage
{
    public static function getLongLanguage(string $shortLanguage): string
    {
        return config('zc-translate.languages.' . $shortLanguage);
    }
}
