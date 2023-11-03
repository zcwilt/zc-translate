<?php

namespace App\Console\Commands\Library;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class LangFileConverter extends FileConverter
{
    const TYPE_NO_CONVERSION = 'NoConversion';

    const PATTERN_TESTS = [
        'SimpleDefine' => '/^(\s*)\'([A-Z0-9_]+)\'\s*=>\s*(.*),$/',
        'ExtraDefine' => '/^(\s*)(\$define\[\'([a-zA-Z0-9_]+)\'\])\s*=\s*(.*);$/',
        'Comment' => '/^\s*\/\//',
        'Locale' => '/^\$locales\s*=\s*/',
    ];

    public function process(): void
    {
        $this->loadOverrides();
        $fileLength = count($this->unconvertedFile);

        foreach ($this->unconvertedFile as $index => $line) {
            $this->command->info('Converting line ' . ($index + 1) . ' of ' . $fileLength);
            $this->convertedFile .= $this->processLine($line);
        }
    }

    private function processLine(string $line): string
    {
        $lineType = $this->getLineType($line);
        $conversionMethod = 'convert' . $lineType;

        $line = method_exists($this, $conversionMethod)
            ? $this->$conversionMethod($line)
            : $line . PHP_EOL;

        return $line;

    }

    private function getLineType(string $line): string
    {
        foreach (self::PATTERN_TESTS as $type => $pattern) {
            if (preg_match($pattern, $line)) {
                return $type;
            }
        }
        return self::TYPE_NO_CONVERSION;
    }

    private function convertSimpleDefine(string $line): string
    {
        $overrides = $this->overrides['SimpleDefine'] ?? [];
        preg_match(self::PATTERN_TESTS['SimpleDefine'], $line, $matches);
        [,$leadingWhitespace, $beforeArrow, $toConvert] = $matches;
        if (isset($overrides[$beforeArrow])) {
            $converted = "'" . $overrides[$beforeArrow] . "'";
        } else {
            $converted = $this->parseAndConvert($toConvert);
        }
        return $leadingWhitespace . "'$beforeArrow' => " . $converted . "," . PHP_EOL;
    }

    private function convertExtraDefine(string $line): string
    {
        preg_match(self::PATTERN_TESTS['ExtraDefine'], $line, $matches);
        [,$leadingWhitespace, $beforeEqual, , $toConvert] = $matches;
        $converted = $this->parseAndConvert($toConvert);
        return "$leadingWhitespace$beforeEqual = $converted;" . PHP_EOL;
    }

    private function convertLocale(string $line): string
    {
        $locale = $line;
        if (isset($this->overrides['locales'])) {
            $locale = '$locales = ' . $this->overrides['locales'] . ';' . PHP_EOL;
        }
        return $locale;
    }

    private function parseAndConvert(string $toConvert): string
    {
        $toConvertParts = explode(' . ', $toConvert);
        foreach ($toConvertParts as $index => $part) {
            $toConvertParts[$index] = $this->convertPart($part);
        }
        $converted = implode(' . ', $toConvertParts);
        return $converted;
    }

    protected function convertPart(string $part): string
    {
        if ($part[0] !== "'" || $part[strlen($part) - 1] !== "'") {
            return $part;
        }
        $part = preg_replace("/^'(.*?)'$/", '$1', $part);
        $part = stripslashes($part);
        $part = $this->getTranslation($part);
        $part = str_replace("'", "\\'", $part);
        $part = "'$part'";
        return $part;
    }

    protected function getTranslation(string $part): string
    {
        $key = 'translated-text-' . $this->sourceLanguage . '-' . $this->targetLanguage . '-' . md5($part);
        if (Cache::has($key)) {
            return Cache::get($key);
        }
        $translation = $this->translator->translate($this->sourceLanguage, $this->targetLanguage, $part);
        Cache::put($key, $translation, now()->addDays(1));
        return $translation;
    }


    private function convertComment(string $line): string
    {
        return $this->translator->translate($this->sourceLanguage, $this->targetLanguage, $line) . PHP_EOL;
    }

    protected function loadOverrides()
    {
        $this->overrides = config('translations.' . $this->targetLanguage . '.overrides.lang');
    }
}
