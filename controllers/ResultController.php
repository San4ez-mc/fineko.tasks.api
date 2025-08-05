<?php

namespace app\controllers;

use yii\rest\ActiveController;
use yii\filters\Cors;

class ResultController extends ActiveController
{
    public $modelClass = 'app\models\Result';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Дозволяємо CORS для фронтенду
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'], // або ['https://ftasks.local'] якщо хочеш тільки свій домен
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 3600,
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];

        return $behaviors;
    }

}
