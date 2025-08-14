<?php

namespace app\controllers;

use app\models\Result;
use Yii;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class ResultController extends ApiController
{
    public function behaviors()
    {
        $b = parent::behaviors();
        $b['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index' => ['GET'],
                'view' => ['GET'],
                'create' => ['POST'],
                'update' => ['PUT', 'PATCH'],
                'delete' => ['DELETE'],
            ],
        ];

        return $b;
    }

    public function actionIndex()
    {
        $userId = (int) Yii::$app->user->id;
        $assignedTo = Yii::$app->request->get('assigned_to');
        $mine = Yii::$app->request->get('mine', '1');

        $q = Result::find();

        if ($assignedTo !== null && $assignedTo !== '') {
            // за потреби — перевір ролі:
            // if (!Yii::$app->user->can('admin')) throw new ForbiddenHttpException('Forbidden');
            $q->andWhere(['assigned_to' => (int) $assignedTo]);
        } elseif ($mine !== '0') {
            $q->andWhere(['assigned_to' => $userId]);
        }

        $page = max(1, (int) Yii::$app->request->get('page', 1));
        $perPage = min(100, max(1, (int) Yii::$app->request->get('per-page', 20)));

        $total = (clone $q)->count();
        $items = $q->orderBy(['id' => SORT_DESC])
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->all();

        return [
            'meta' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => (int) $total,
            ],
            'items' => array_map(function (Result $m) {
                return $m->toArray([], ['assignee', 'setter']);
            }, $items),
        ];
    }

    public function actionView($id)
    {
        $m = $this->findModel((int) $id);
        $this->ensureCanView($m);
        return $m->toArray([], ['assignee', 'setter']);
    }

    public function actionCreate()
    {
        $m = new Result();
        $data = Yii::$app->request->post();

        $m->load($data, '');

        $m->urgent = isset($data['urgent']) ? filter_var($data['urgent'], FILTER_VALIDATE_BOOLEAN) : false;
        if (isset($data['responsible_id'])) {
            $m->assigned_to = (int) $data['responsible_id'];
        }
        if (isset($data['final_result'])) {
            $m->expected_result = $data['final_result'];
        }

        if ($m->save()) {
            Yii::$app->response->statusCode = 201;
            return $m->toArray([], ['assignee', 'setter']);
        }
        Yii::$app->response->statusCode = 422;
        return ['errors' => $m->getErrors()];
    }

    public function actionUpdate($id)
    {
        $m = $this->findModel((int) $id);
        $this->ensureCanEdit($m);

        $data = Yii::$app->request->post();

        $m->load($data, '');

        $m->urgent = isset($data['urgent']) ? filter_var($data['urgent'], FILTER_VALIDATE_BOOLEAN) : false;
        if (isset($data['responsible_id'])) {
            $m->assigned_to = (int) $data['responsible_id'];
        }
        if (isset($data['final_result'])) {
            $m->expected_result = $data['final_result'];
        }

        if (array_key_exists('completed_at', $data)) {
            if ($data['completed_at'] === null || $data['completed_at'] === '') {
                $m->completed_at = null;
            } else {
                $ts = strtotime($data['completed_at']);
                if ($ts === false) {
                    Yii::$app->response->statusCode = 422;
                    return ['errors' => ['completed_at' => ['Invalid date format']]];
                }
                $m->completed_at = $ts;
            }
        }

        if ($m->save()) {
            return $m->toArray([], ['assignee', 'setter']);
        }
        Yii::$app->response->statusCode = 422;
        return ['errors' => $m->getErrors()];
    }

    public function actionDelete($id)
    {
        $m = $this->findModel((int) $id);
        $this->ensureCanEdit($m);
        $m->delete();
        return ['success' => true];
    }

    protected function findModel(int $id): Result
    {
        $m = Result::findOne($id);
        if (!$m)
            throw new NotFoundHttpException('Result not found');
        return $m;
    }

    protected function ensureCanView(Result $m): void
    {
        $uid = (int) Yii::$app->user->id;
        if (in_array($uid, array_filter([$m->created_by, $m->assigned_to, $m->setter_id]), true)) {
            return;
        }
        // if (Yii::$app->user->can('admin')) return;
        // Додай власну RBAC/організаційну перевірку за потреби.
    }

    protected function ensureCanEdit(Result $m): void
    {
        $uid = (int) Yii::$app->user->id;
        if ((int) $m->setter_id === $uid)
            return;
        // if (Yii::$app->user->can('admin')) return;
        throw new ForbiddenHttpException('Forbidden');
    }
}
