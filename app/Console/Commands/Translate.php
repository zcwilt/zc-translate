<?php

namespace App\Console\Commands;

use App\Console\Commands\Library\APIFileManager;
use App\Console\Commands\Library\ShortToLongLanguage;
use Illuminate\Console\Command;

class translate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:all {source-language} {target-language} {--source-dir-prefix=} {--branch=master} {--rebuild }';
    protected $description = 'Translate text from one language to another language using google translate.' .
    PHP_EOL .
    'Usage: php artisan translate:all {source-language} {target-language} {--branch=master} {--source_dir=} ' .
    PHP_EOL .
    'where --source_dir is either empty for store languages, or "admin" for admin languages' .
    PHP_EOL .
    'Note: source-language and target-language should be short ISO codes, e.g. en fre de es etc.';

    protected bool $debug = false;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('rebuild')) {
            $this->call('cache:clear');
            $this->removeDirectory(storage_path('app/translated'));
        }
        $sourceLanguageShort = $this->argument('source-language');
        $targetLanguageShort = $this->argument('target-language');
        $sourceLanguageLong = ShortToLongLanguage::getLongLanguage($sourceLanguageShort);
        $targetLanguageLong = ShortToLongLanguage::getLongLanguage($targetLanguageShort);
        if (!isset($sourceLanguageLong) || !isset($targetLanguageLong)) {
            $this->error('Invalid source or target language. Ensure that the language is defined in config/translations/{language}.php');
            return;
        }
        $sourceDir = $this->option('source-dir-prefix');
        $branch = $this->option('branch');
        $fileManager = new APIFileManager($this, $sourceLanguageLong, $targetLanguageLong, $sourceDir, $branch);
        $mainFile = $fileManager->getMainFile();
        $convertedFile = $this->processFile($mainFile, $sourceLanguageShort, $targetLanguageShort);
        $fileManager->putConvertedFile($mainFile, $convertedFile);
        $files = $fileManager->getLanguageDirectoryFiles();
        foreach ($files as $file) {
            $convertedFile = $this->processFile($file, $sourceLanguageShort, $targetLanguageShort);
            $fileManager->putConvertedFile($file, $convertedFile);
        }
    }

    protected function processFile(array $sourceFile, string $sourceLanguage, string $targetLanguage): string
    {
        $this->info('Processing file ' . $sourceFile['path']);
        $fileType = $this->getFileType($sourceFile['name']);
        $this->info('File Type ' . $fileType);
        if ($fileType === 'unknown') {
            return $sourceFile['content'];
        }
        $fileConverterClass = 'App\Console\Commands\Library\\' . ucfirst($fileType) . 'FileConverter';
        $this->info('File Converter Class ' . $fileConverterClass);
        $fileConverter = new $fileConverterClass(base64_decode($sourceFile['content']), $sourceLanguage, $targetLanguage, $this);
        $fileConverter->process();
        $convertedFile = $fileConverter->getConvertedFile();
        return $convertedFile;
    }

    protected function getFileType(string $sourceFile): string
    {
        $fileType = 'unknown';
        if (strpos($sourceFile, 'lang.') !== false) {
            $fileType = 'lang';
        }
        if (strpos($sourceFile, 'define') !== false) {
            $fileType = 'define';
        }
        return $fileType;
    }

    protected function removeDirectory(String $path)
    {
        $this->info('Removing directory ' . $path);
        if (is_dir($path)) {
            $objects = scandir($path);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    if (is_dir($path . '/' . $object)) {
                        $this->removeDirectory($path . '/' . $object);
                    } else {
                        unlink($path . '/' . $object);
                    }
                }
            }
            rmdir($path);
        }
    }
}
