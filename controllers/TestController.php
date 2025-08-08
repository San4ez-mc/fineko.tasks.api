<?php

namespace app\controllers;

use Yii;
use yii\web\Response;

class TestController extends ApiController
{
    /**
     * GET /test/ping
     * Тестовий ендпойнт, захищений Bearer токеном
     */
    public function actionPing()
    {
        return [
            'success' => true,
            'message' => 'Токен валідний',
            'user_id' => Yii::$app->user->id,
        ];
    }
}
