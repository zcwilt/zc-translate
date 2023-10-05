<?php

namespace App\Console\Commands\Library;

use GuzzleHttp\Client;

class GoogleTranslateV2
{

    const GOOGLE_V2_TRANSLATE_URL = 'https://translation.googleapis.com/language/translate/v2';

    public function __construct()
    {
        $this->apiKey = env('GOOGLE_TRANSLATE_API_KEY', '');
    }

    public function translate(string $sourceLanguage, string $targetLanguage, string $text): string
    {
        $url = self::GOOGLE_V2_TRANSLATE_URL;
        $client = new Client();
        $response = $client->request('POST', $url, [
            'form_params' => [
                'q' => urlencode($text),
                'target' => $targetLanguage,
                'source' => $sourceLanguage,
                'key' => $this->apiKey,
            ],
        ]);

        $body = $response->getBody();
        $responseData = json_decode($body, true);
        try {
            $responseData = $responseData['data']['translations'][0]['translatedText'];
            $responseData = urldecode(html_entity_decode($responseData));
        } catch (Exception $e) {
            // do nothing
        }
        return $responseData;
    }
}
