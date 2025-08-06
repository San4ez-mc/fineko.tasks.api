<?php
namespace app\controllers;

use yii\rest\ActiveController;
use yii\filters\Cors;

class UserController extends ActiveController
{
    public $modelClass = 'app\models\User';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['https://ftasks.local', 'https://tasks.fineko.space'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 3600,
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];
        return $behaviors;
    }
}
