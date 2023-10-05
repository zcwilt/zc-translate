<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;

class TranslateText extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:text{source} {target}';
    protected $description = 'Translate text to another language using google translate';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $text = $this->ask('Text to translate');
        $target = $this->argument('target');
        $source = $this->argument('source');
        $this->info('Translating ' . $text . ' to ' . $target);
        $client = new Client();
        $response = $client->request('POST', 'https://translation.googleapis.com/language/translate/v2', [
            'form_params' => [
                'q' => $text,
                'target' => $target,
                'source' => $source,
                'key' => env('GOOGLE_TRANSLATE_API_KEY', ''),
            ],
        ]);
        $response = json_decode($response->getBody()->getContents());
        $this->info($response->data->translations[0]->translatedText);
    }
}
