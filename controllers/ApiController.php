<?php

namespace app\controllers;

use Yii;
use yii\rest\Controller;
use yii\filters\Cors;
use yii\filters\auth\HttpBearerAuth;

class ApiController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // 1) CORS має бути ДО автентифікатора
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                // Дозволені фронтенд-ориджі (додай інші за потреби)
                'Origin' => ['https://ftasks.local', 'https://tasks.fineko.space'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 86400,
            ],
        ];

        // 2) Bearer-автентифікація — після CORS; preflight виключаємо
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['options'], // інші винятки (login/refresh) задаємо в конкретних контролерах
        ];

        return $behaviors;
    }

    // 3) Явно віддаємо 200 на preflight, не чіпаючи автентифікацію
    public function beforeAction($action)
    {
        if (Yii::$app->request->isOptions) {
            Yii::$app->response->statusCode = 200;
            return false;
        }
        return parent::beforeAction($action);
    }

    // 4) Додаємо стандартну OptionsAction на випадок прямого виклику /xxx/options
    public function actions()
    {
        return [
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
        ];
    }
}
