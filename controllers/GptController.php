<?php

namespace app\controllers;

use Yii;
use yii\web\BadRequestHttpException;
use yii\web\UploadedFile;
use app\services\AiAudioTranscriber;
use app\services\AiJsonExtractor;

/**
 * Controller for interacting with OpenAI (GPT) services.
 */
class GptController extends ApiController
{
    private AiAudioTranscriber $transcriber;
    private AiJsonExtractor $extractor;

    public function __construct($id, $module, $config = [])
    {
        $this->transcriber = new AiAudioTranscriber();
        $this->extractor = new AiJsonExtractor();
        parent::__construct($id, $module, $config);
    }

    public function verbs()
    {
        return array_merge(parent::verbs(), [
            'audio-task' => ['POST'],
            'audio-result' => ['POST'],
            'audio-template' => ['POST'],
            'text-task' => ['POST'],
        ]);
    }

    private function transcribeFile(): string
    {
        $file = UploadedFile::getInstanceByName('file');
        if (!$file) {
            throw new BadRequestHttpException('File is required');
        }
        $text = $this->transcriber->transcribe($file);
        if ($text === '') {
            throw new BadRequestHttpException('Unable to transcribe audio');
        }
        return $text;
    }

    /**
     * Converts audio to task JSON.
     */
    public function actionAudioTask()
    {
        $text = $this->transcribeFile();
        return $this->extractor->extract($text, 'Extract task fields from the text and return them as JSON.');
    }

    /**
     * Converts audio to result JSON.
     */
    public function actionAudioResult()
    {
        $text = $this->transcribeFile();
        return $this->extractor->extract($text, 'Extract task result fields from the text and return them as JSON.');
    }

    /**
     * Converts audio to task template JSON.
     */
    public function actionAudioTemplate()
    {
        $text = $this->transcribeFile();
        return $this->extractor->extract($text, 'Extract task template fields from the text and return them as JSON.');
    }

    /**
     * Checks whether given text describes a task and returns task JSON if so.
     */
    public function actionTextTask()
    {
        $text = Yii::$app->request->post('text', '');
        if ($text === '') {
            throw new BadRequestHttpException('Parameter "text" is required');
        }
        $data = $this->extractor->extract(
            $text,
            'Determine if the user text describes a task. If yes, respond with {"is_task":true, ...task fields...}. If not, respond with {"is_task":false}.'
        );
        if (!($data['is_task'] ?? false)) {
            return [];
        }
        return $data;
    }
}
