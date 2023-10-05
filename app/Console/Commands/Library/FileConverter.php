<?php

namespace App\Console\Commands\Library;

use Illuminate\Console\Command;

class FileConverter
{
    protected string $sourceFile;
    protected string $sourceLanguage;
    protected string $targetLanguage;
    protected array $unconvertedFile;
    protected string $convertedFile = '';
    protected Command $command;
    protected GoogleTranslateV2 $translator;


    public function __construct(
        string $sourceFile,
        string $sourceLanguage,
        string $targetLanguage,
        Command $command,
    ) {
        $this->unconvertedFile = explode(PHP_EOL, $sourceFile);
        $this->sourceLanguage = $sourceLanguage;
        $this->targetLanguage = $targetLanguage;
        $this->command = $command;
        $this->translator = new GoogleTranslateV2();
    }

    public function getConvertedFile(): string
    {
        return $this->convertedFile;
    }
}
