<?php

namespace app\controllers;

use app\services\AiTaskExtractor;
use app\services\ResultService;
use Yii;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;

class TelegramController extends ApiController
{
    public function behaviors()
    {
        $b = parent::behaviors();
        $b['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'webhook' => ['POST'],
                'health' => ['GET'],
            ],
        ];
        return $b;
    }

    public function actionWebhook()
    {
        $data = Yii::$app->request->getBodyParams();
        if (empty($data)) {
            throw new BadRequestHttpException('Empty payload');
        }

        // placeholder logic
        $text = $data['message']['text'] ?? '';
        $chatId = $data['message']['chat']['id'] ?? null;
        if (!$chatId) {
            return ['ok' => false];
        }

        $ai = new AiTaskExtractor();
        $resultService = new ResultService();
        $aiData = $ai->extract(0, $text, []);
        if (($aiData['is_task'] ?? false) && empty($aiData['missing'])) {
            $result = $resultService->createFromTelegram($aiData);
            return ['ok' => true, 'result_id' => $result ? $result->id : null];
        }

        return ['ok' => true];
    }

    public function actionHealth()
    {
        return ['status' => 'ok'];
    }
}
