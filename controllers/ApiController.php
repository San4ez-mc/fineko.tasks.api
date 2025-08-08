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

        // 1) CORS — ОБОВʼЯЗКОВО ДО автентифікатора
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                // Фронтенд-ориджі, з яких дозволяємо звернення
                'Origin' => ['https://ftasks.local', 'https://tasks.fineko.space'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'], // або вкажи список: ['Authorization','Content-Type','X-Requested-With']
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 86400,
            ],
        ];

        // 2) Bearer-автентифікація — ПІСЛЯ CORS
        //    preflight (OPTIONS) не потребує токена
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['options'], // для login/refresh винятки робимо в їхньому контролері
        ];

        return $behaviors;
    }

    /**
     * Віддаємо 200 на preflight (OPTIONS) та не заходимо в екшени/автентифікатор.
     */
    public function beforeAction($action)
    {
        if (Yii::$app->request->isOptions) {
            Yii::$app->response->statusCode = 200;
            // Додатково гарантуємо заголовки (на випадок, якщо вебсервер їх не підставив)
            $origin = Yii::$app->request->headers->get('Origin');
            if (in_array($origin, ['https://ftasks.local', 'https://tasks.fineko.space'], true)) {
                $h = Yii::$app->response->headers;
                $h->set('Access-Control-Allow-Origin', $origin);
                $h->set('Access-Control-Allow-Credentials', 'true');
                $h->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
                $h->set('Access-Control-Allow-Headers', 'Authorization, Content-Type, X-Requested-With');
                $h->set('Vary', 'Origin');
            }
            return false;
        }
        return parent::beforeAction($action);
    }

    /**
     * На випадок прямого звернення до /xxx/options
     */
    public function actions()
    {
        return [
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
        ];
    }
}
