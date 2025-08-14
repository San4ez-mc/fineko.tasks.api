<?php

namespace app\services;

use Yii;
use yii\httpclient\Client;

/**
 * Generic helper for sending text to OpenAI and receiving structured JSON back.
 */
class AiJsonExtractor
{
    /**
     * Calls OpenAI chat completion API with given system prompt and user text.
     *
     * @param string $text
     * @param string $systemPrompt
     * @return array
     */
    public function extract(string $text, string $systemPrompt): array
    {
        $apiKey = Yii::$app->params['openaiApiKey'] ?? null;
        if (!$apiKey) {
            return [];
        }

        $client = new Client(['baseUrl' => 'https://api.openai.com/v1']);
        $response = $client->post('chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $text],
            ],
        ])->addHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
        ])->send();

        if (!$response->isOk) {
            Yii::error('OpenAI error: ' . $response->content, 'application');
            return [];
        }

        $raw = $response->data['choices'][0]['message']['content'] ?? '{}';
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
