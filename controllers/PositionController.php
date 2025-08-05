<?php
namespace app\controllers;

use yii\rest\ActiveController;
use yii\filters\Cors;

class PositionController extends ActiveController
{
    public $modelClass = 'app\models\Position';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 3600,
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];
        return $behaviors;
    }
}
