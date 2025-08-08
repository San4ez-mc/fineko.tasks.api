<?php

namespace app\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use app\models\Result;

class ResultController extends ApiController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
        ];

        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['options'], // preflight без авторизації
        ];

        return $behaviors;
    }

    public function verbs()
    {
        return [
            'options' => ['OPTIONS'],
            'index' => ['GET'],
            'view' => ['GET'],
            'create' => ['POST'],
            'update' => ['PATCH', 'PUT'],
            'delete' => ['DELETE'],
            'complete' => ['POST'],
        ];
    }

    public function actionOptions()
    {
        return 'ok';
    }

    /**
     * GET /results
     * Повертає тільки результати поточного користувача
     */
    public function actionIndex()
    {
        return new ActiveDataProvider([
            'query' => Result::find()->where(['user_id' => Yii::$app->user->id]),
            'pagination' => [
                'pageSize' => (int) Yii::$app->request->get('per-page', 25),
            ],
        ]);
    }

    /**
     * GET /results/{id}
     */
    public function actionView($id)
    {
        $model = Result::find()
            ->where(['id' => (int) $id, 'user_id' => Yii::$app->user->id])
            ->one();

        if (!$model) {
            throw new NotFoundHttpException('Result not found.');
        }
        return $model;
    }

    /**
     * POST /results
     * body: { title, description?, expected_result?, deadline?, parent_id? }
     */
    public function actionCreate()
    {
        $model = new Result();
        $model->load(Yii::$app->request->bodyParams, '');
        $model->user_id = Yii::$app->user->id;

        if ($model->save()) {
            return $model;
        }
        return ['success' => false, 'errors' => $model->getErrors()];
    }

    /**
     * PATCH /results/{id}
     * body: часткове оновлення
     */
    public function actionUpdate($id)
    {
        $model = Result::find()
            ->where(['id' => (int) $id, 'user_id' => Yii::$app->user->id])
            ->one();

        if (!$model) {
            throw new NotFoundHttpException('Result not found.');
        }

        $model->load(Yii::$app->request->bodyParams, '');
        if ($model->save()) {
            return $model;
        }
        return ['success' => false, 'errors' => $model->getErrors()];
    }

    /**
     * DELETE /results/{id}
     */
    public function actionDelete($id)
    {
        $model = Result::find()
            ->where(['id' => (int) $id, 'user_id' => Yii::$app->user->id])
            ->one();

        if (!$model) {
            throw new NotFoundHttpException('Result not found.');
        }

        if ($model->delete() !== false) {
            return ['success' => true];
        }
        return ['success' => false];
    }

    /**
     * POST /results/{id}/complete
     * body: { is_completed: true|false }
     */
    public function actionComplete($id)
    {
        $isCompleted = Yii::$app->request->bodyParams['is_completed'] ?? null;
        if ($isCompleted === null) {
            throw new BadRequestHttpException('Field "is_completed" is required.');
        }

        $model = Result::find()
            ->where(['id' => (int) $id, 'user_id' => Yii::$app->user->id])
            ->one();

        if (!$model) {
            throw new NotFoundHttpException('Result not found.');
        }

        $model->is_completed = (bool) $isCompleted;
        if ($model->save(false)) {
            return ['success' => true, 'is_completed' => $model->is_completed];
        }
        return ['success' => false];
    }
}
