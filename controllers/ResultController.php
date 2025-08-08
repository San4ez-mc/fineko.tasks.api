<?php

namespace app\controllers;

use app\models\Result;
use app\models\search\ResultSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ResultController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // CORS
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['https://tasks.fineko.space', 'http://localhost', 'http://127.0.0.1'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index' => ['GET', 'OPTIONS'],
                'view' => ['GET', 'OPTIONS'],
                'create' => ['POST', 'OPTIONS'],
                'update' => ['PUT', 'PATCH', 'OPTIONS'],
                'delete' => ['DELETE', 'OPTIONS'],
                'complete' => ['PATCH', 'POST', 'OPTIONS'], // POST як запаска
            ],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => ['index', 'view', 'create', 'update', 'delete', 'complete'],
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $search = new ResultSearch();
        $provider = $search->search(Yii::$app->request->queryParams);

        return [
            'items' => array_map(function (Result $m) {
                return $m->toArray(); }, $provider->getModels()),
            'pagination' => [
                'totalCount' => $provider->getTotalCount(),
                'pageSize' => $provider->getPagination()->getPageSize(),
                'page' => $provider->getPagination()->getPage() + 1,
                'pageCount' => $provider->getPagination()->getPageCount(),
            ],
        ];
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $model->toArray([], ['children', 'tasks']);
    }

    public function actionCreate()
    {
        $body = Yii::$app->request->bodyParams;
        $model = new Result();

        if (!Yii::$app->user->isGuest && property_exists(Yii::$app->user->identity, 'organization_id')) {
            $model->organization_id = (int) Yii::$app->user->identity->organization_id;
        } elseif (isset($body['organization_id'])) {
            $model->organization_id = (int) $body['organization_id'];
        }

        // created_by ставиться в beforeValidate з user->id
        $model->parent_id = $body['parent_id'] ?? null;
        $model->title = $body['title'] ?? null;
        $model->description = $body['description'] ?? null;
        $model->expected_result = $body['expected_result'] ?? null;
        $model->deadline = $body['deadline'] ?? null; // 'YYYY-MM-DD'

        if ($model->save()) {
            return $model->toArray();
        }

        Yii::$app->response->statusCode = 422;
        return ['errors' => $model->getErrors()];
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $body = Yii::$app->request->bodyParams;

        $model->parent_id = array_key_exists('parent_id', $body) ? $body['parent_id'] : $model->parent_id;
        $model->title = $body['title'] ?? $model->title;
        $model->description = $body['description'] ?? $model->description;
        $model->expected_result = $body['expected_result'] ?? $model->expected_result;
        $model->deadline = $body['deadline'] ?? $model->deadline;

        if ($model->save()) {
            return $model->toArray();
        }

        Yii::$app->response->statusCode = 422;
        return ['errors' => $model->getErrors()];
    }

    public function actionComplete($id)
    {
        $model = $this->findModel($id);
        $body = Yii::$app->request->bodyParams;

        if (!array_key_exists('is_completed', $body)) {
            throw new BadRequestHttpException('Missing is_completed');
        }
        $model->completed_at = $body['is_completed'] ? time() : null;

        if ($model->save(false, ['completed_at', 'updated_at'])) {
            return $model->toArray();
        }

        Yii::$app->response->statusCode = 422;
        return ['errors' => $model->getErrors()];
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->delete() !== false) {
            return ['success' => true];
        }
        Yii::$app->response->statusCode = 500;
        return ['success' => false];
    }

    protected function findModel($id): Result
    {
        $model = Result::findOne((int) $id);
        if (!$model) {
            throw new NotFoundHttpException('Result not found');
        }
        return $model;
    }
}
