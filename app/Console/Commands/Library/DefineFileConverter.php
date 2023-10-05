<?php

namespace App\Console\Commands\Library;

use Illuminate\Console\Command;

class DefineFileConverter extends FileConverter
{

    public function process(): void
    {
        $fileLength = count($this->unconvertedFile);

        foreach ($this->unconvertedFile as $index => $line) {
            $this->command->info('Converting line ' . ($index + 1) . ' of ' . $fileLength);
            $this->convertedFile .= $this->processLine($line);
        }
    }

    private function processLine(string $line): string
    {

        $translation = $this->translator->translate($this->sourceLanguage, $this->targetLanguage, $line);
        return $translation;
    }

}
