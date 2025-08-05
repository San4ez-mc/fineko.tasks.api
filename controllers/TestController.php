<?php
namespace app\controllers;

use yii\web\Controller;
use yii\web\Response;
use yii\filters\Cors;

class TestController extends Controller
{
    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => Cors::class,
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'OPTIONS'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Max-Age' => 3600,
                    'Access-Control-Request-Headers' => ['*'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return ['message' => 'API is working successfully'];
    }
}
