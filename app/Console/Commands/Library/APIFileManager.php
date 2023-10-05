<?php

namespace App\Console\Commands\Library;

use GrahamCampbell\GitHub\Facades\GitHub;
use Illuminate\Support\Facades\Cache;

class APIFileManager
{

    public function __construct($console, $sourceLanguageLong, $targetLanguageLong, $sourceDir, $branch)
    {
        $this->console = $console;
        $this->sourceLanguageLong = $sourceLanguageLong;
        $this->targetLanguageLong = $targetLanguageLong;
        $this->sourceDir = $sourceDir;
        $this->branch = $branch;
    }

    public function getMainFile(): array
    {
        $rootPath = $this->sourceDir . 'includes/languages/lang.' . $this->sourceLanguageLong . '.php';
        $cacheKey = 'github-file-' . $rootPath . $this->branch;
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        $contents = GitHub::repo()->contents()->show('zencart', 'zencart', $rootPath, $this->branch);
        $amendPath = dirname($contents['path']) . '/lang.' . $this->targetLanguageLong . '.php';
        $contents['target_filepath'] = $amendPath;
        Cache::put($cacheKey, $contents, now()->addDays(1));
        return $contents;
    }

    public function putConvertedFile(array $file, string $convertedFile)
    {
        $filePath = $file['target_filepath'];
        $storagePath = storage_path('app/translated/' . $filePath );
        if (!is_dir(dirname($storagePath))) {
            mkdir(dirname($storagePath), 0777, true);
        }
        file_put_contents($storagePath, $convertedFile);
    }

    public function getLanguageDirectoryFiles(): array
    {
        $rootPath = $this->sourceDir . 'includes/languages/' . $this->sourceLanguageLong;
        $this->downloadedFiles = [];
        $this->downloadDirectory($rootPath);
        return $this->downloadedFiles;
    }

    protected function downloadDirectory(string $path)
    {
        $this->console->info('Downloading directory ' . $path);
        $contents = GitHub::repo()->contents()->show('zencart', 'zencart', $path, $this->branch);
        foreach($contents as $content) {
            if ($content['type'] === 'dir') {
                $this->downloadDirectory($content['path']);
            } elseif ($content['type'] === 'file') {
                $fileContent = $this->downloadFile($content['path']);
                $this->downloadedFiles[] = $fileContent;
            }
        }
    }

    protected function downloadFile(string $path): array
    {
        $this->console->info('Downloading file ' . $path);
        $cacheKey = 'github-file-' . $path . $this->branch;
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        $contents = GitHub::repo()->contents()->show('zencart', 'zencart', $path, $this->branch);
        $filePath = $contents['path'];
        $filePath = str_replace('includes/languages/' . $this->sourceLanguageLong, 'includes/languages/' . $this->targetLanguageLong, $filePath);
        $contents['target_filepath'] = $filePath;

        return $contents;
    }
}
