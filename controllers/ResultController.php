<?php

namespace app\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use app\models\Result;

class ResultController extends ApiController
{
    public $modelClass = 'app\models\Result';

    /**
     * GET /results — лише свої
     */
    public function actionIndex()
    {
        return new ActiveDataProvider([
            'query' => Result::find()->where(['user_id' => Yii::$app->user->id]),
            'pagination' => ['pageSize' => 100],
        ]);
    }

    /**
     * POST /results — створення результату
     */
    public function actionCreate()
    {
        $model = new Result();
        $model->load(Yii::$app->request->bodyParams, '');
        $model->user_id = Yii::$app->user->id;

        if ($model->save()) {
            return $model;
        }

        return $model->getErrors();
    }

    /**
     * GET /results/{id}
     */
    public function actionView($id)
    {
        return Result::find()
            ->where(['id' => $id, 'user_id' => Yii::$app->user->id])
            ->one();
    }

    /**
     * PUT/PATCH /results/{id}
     */
    public function actionUpdate($id)
    {
        $model = Result::find()
            ->where(['id' => $id, 'user_id' => Yii::$app->user->id])
            ->one();

        if (!$model) {
            return ['error' => 'Not found'];
        }

        $model->load(Yii::$app->request->bodyParams, '');
        if ($model->save()) {
            return $model;
        }

        return $model->getErrors();
    }

    /**
     * DELETE /results/{id}
     */
    public function actionDelete($id)
    {
        $model = Result::find()
            ->where(['id' => $id, 'user_id' => Yii::$app->user->id])
            ->one();

        if ($model && $model->delete()) {
            return ['success' => true];
        }

        return ['success' => false];
    }
}
