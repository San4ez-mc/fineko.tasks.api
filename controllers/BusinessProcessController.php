<?php

namespace app\controllers;

use app\models\BusinessProcess;
use Yii;
use yii\filters\VerbFilter;

class BusinessProcessController extends ApiController
{
    public function behaviors()
    {
        $b = parent::behaviors();
        $b['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'create' => ['POST'],
            ],
        ];
        return $b;
    }

    public function actionCreate()
    {
        $model = new BusinessProcess();
        $model->load(Yii::$app->request->post(), '');
        if ($model->save()) {
            Yii::$app->response->statusCode = 201;
            return $model;
        }
        Yii::$app->response->statusCode = 422;
        return ['errors' => $model->getErrors()];
    }
}
