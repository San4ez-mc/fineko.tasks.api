<?php

namespace app\services;

use Yii;
use yii\httpclient\Client;

class AiTaskExtractor
{
    /**
     * Calls OpenAI API to extract task fields from given text.
     *
     * @param int $companyId
     * @param string $text
     * @param array $context
     * @return array
     */
    public function extract(int $companyId, string $text, array $context = []): array
    {
        $client = new Client(['baseUrl' => 'https://api.openai.com/v1']);
        $apiKey = Yii::$app->params['openaiApiKey'] ?? null;
        if (!$apiKey) {
            return ['is_task' => false, 'missing' => ['openaiApiKey']];
        }

        $response = $client->post('chat/completions', [
            'model' => 'gpt-3.5-turbo-0125',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a task extraction assistant.'],
                ['role' => 'user', 'content' => $text],
            ],
        ])->addHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
        ])->send();

        if (!$response->isOk) {
            Yii::error('OpenAI error: ' . $response->content, 'application');
            return ['is_task' => false, 'missing' => ['openai']];
        }

        // This is a placeholder. In production parse $response->data
        return $response->data['choices'][0]['message']['content'] ?? [];
    }
}
