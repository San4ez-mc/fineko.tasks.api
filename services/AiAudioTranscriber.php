<?php

namespace app\services;

use Yii;
use yii\httpclient\Client;
use yii\web\UploadedFile;

/**
 * Service that sends audio files to OpenAI and returns transcribed text.
 */
class AiAudioTranscriber
{
    /**
     * Transcribe given audio file via OpenAI API.
     *
     * @param UploadedFile $file
     * @return string Transcribed text or empty string on failure.
     */
    public function transcribe(UploadedFile $file): string
    {
        $apiKey = Yii::$app->params['openaiApiKey'] ?? null;
        if (!$apiKey) {
            return '';
        }

        $client = new Client(['baseUrl' => 'https://api.openai.com/v1']);
        $request = $client->createRequest()
            ->setMethod('POST')
            ->setUrl('audio/transcriptions')
            ->addHeaders(['Authorization' => 'Bearer ' . $apiKey])
            ->addFile('file', $file->tempName, $file->type, $file->name)
            ->setData(['model' => 'gpt-4o-mini-transcribe']);

        $response = $request->send();
        if (!$response->isOk) {
            Yii::error('OpenAI transcription error: ' . $response->content, 'application');
            return '';
        }

        return $response->data['text'] ?? '';
    }
}
