<?php

namespace app\controllers;

use app\models\Task;
use Yii;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class TaskController extends ApiController
{
    /**
     * Дії, які доступні без Bearer-автентифікації.
     */
    protected function authExcept(): array
    {
        return array_merge(parent::authExcept(), ['filter', 'templates']);
    }

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
                'filter' => ['GET'],
                'templates' => ['GET'],
                'daily' => ['GET', 'POST'],
            ],
        ];

        return $b;
    }

    public function actionIndex()
    {
        $userId = (int) Yii::$app->user->id;
        $assignedTo = Yii::$app->request->get('assigned_to');
        $mine = Yii::$app->request->get('mine', '1');

        $q = Task::find();

        if ($assignedTo !== null && $assignedTo !== '') {
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
            'items' => array_map(function (Task $m) {
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
        $m = new Task();
        $m->load(Yii::$app->request->post(), '');

        if ($m->save()) {
            return $m->toArray([], ['assignee', 'setter']);
        }
        Yii::$app->response->statusCode = 422;
        return ['errors' => $m->getErrors()];
    }

    public function actionUpdate($id)
    {
        $m = $this->findModel((int) $id);
        $this->ensureCanEdit($m);

        $m->load(Yii::$app->request->post(), '');
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

    public function actionFilter($date = null)
    {
        if (!$date) {
            throw new BadRequestHttpException('Parameter "date" is required');
        }
        // TODO: повернути список задач за датою
        return [
            'date' => $date,
            'items' => [], // замінити на реальні дані
        ];
    }

    public function actionTemplates()
    {
        // TODO: повернути шаблони задач
        return [
            'items' => [], // замінити на реальні дані
        ];
    }

    public function actionDaily()
    {
        // TODO: повернути задачі за today для поточного користувача
        $today = date('Y-m-d');
        return [
            'date' => $today,
            'items' => [], // замінити на реальні дані
        ];
    }

    protected function findModel(int $id): Task
    {
        $m = Task::findOne($id);
        if (!$m)
            throw new NotFoundHttpException('Task not found');
        return $m;
    }

    protected function ensureCanView(Task $m): void
    {
        $uid = (int) Yii::$app->user->id;
        if (in_array($uid, array_filter([$m->created_by, $m->assigned_to, $m->setter_id]), true)) {
            return;
        }
        // if (Yii::$app->user->can('admin')) return;
    }

    protected function ensureCanEdit(Task $m): void
    {
        $uid = (int) Yii::$app->user->id;
        if ((int) $m->setter_id === $uid)
            return;
        // if (Yii::$app->user->can('admin')) return;
        throw new ForbiddenHttpException('Forbidden');
    }
}
