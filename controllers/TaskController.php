<?php

namespace app\controllers;

use app\models\Task;
use Yii;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\httpclient\Client;
use app\models\TaskComment;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

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
                'daily' => ['POST'],
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
            $this->notifyServer($m);
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
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            throw new UnauthorizedHttpException('Auth required');
        }

        $data = Yii::$app->request->getBodyParams();
        // comments може прийти рядком JSON або масивом
        $commentsRaw = $data['comments'] ?? '[]';
        if (is_string($commentsRaw)) {
            try {
                $comments = json_decode($commentsRaw, true, 512, JSON_THROW_ON_ERROR);
            } catch (\Throwable $e) {
                $comments = [];
            }
        } else {
            $comments = is_array($commentsRaw) ? $commentsRaw : [];
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $task = new Task();
            // МЕПІНГ ПОЛІВ — ПІД ІСНУЮЧУ СХЕМУ
            $task->title = trim((string) ($data['title'] ?? ''));
            $task->type = (string) ($data['type'] ?? 'неважлива нетермінова');
            $task->planned_date = (string) ($data['planned_date'] ?? null); // 'YYYY-MM-DD'
            $task->expected_time = (int) ($data['expected_time'] ?? 0);
            $task->actual_time = (int) ($data['actual_time'] ?? 0);
            $task->expected_result = (string) ($data['expected_result'] ?? '');
            $task->description = (string) ($data['description'] ?? '');
            $task->manager = (string) ($data['manager'] ?? '');
            $task->executor_id = !empty($data['executor_id']) ? (int) $data['executor_id'] : null;
            $task->result_id = !empty($data['result_id']) ? (int) $data['result_id'] : null;

            // дефолтний статус
            if (empty($task->status)) {
                $task->status = 'new';
            }

            if (!$task->validate()) {
                $transaction->rollBack();
                return [
                    'success' => false,
                    'errors' => $task->getErrors(),
                ];
            }

            if (!$task->save(false)) {
                $transaction->rollBack();
                throw new BadRequestHttpException('Cannot save task.');
            }

            // ЗБЕРЕЖЕННЯ КОМЕНТАРІВ (дерево)
            $currentUserId = (int) Yii::$app->user->id;
            $this->saveCommentsRecursive($task->id, $comments, null, $currentUserId);

            $transaction->commit();

            return [
                'success' => true,
                'task' => $this->serializeTask($task),
                'comments' => $this->serializeTaskCommentsTree($task->id),
            ];
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Internal error',
            ];
        }
    }

    /**
     * Зберігає масив коментарів (з фронту) рекурсивно.
     * Очікуваний формат елемента: ['author' => '...', 'text' => '...', 'replies' => [ ... ]]
     */
    private function saveCommentsRecursive(int $taskId, array $comments, ?int $parentId, ?int $fallbackAuthorId): void
    {
        foreach ($comments as $c) {
            if (!isset($c['text']) || $c['text'] === '') {
                continue;
            }
            $comment = new TaskComment();
            $comment->task_id = $taskId;

            // якщо у фронті автор не дає id, збережемо ім’я + поточного користувача як author_id
            $comment->author_id = $fallbackAuthorId ?: null;
            $comment->author_name = isset($c['author']) && is_string($c['author']) ? $c['author'] : null;
            $comment->text = (string) $c['text'];
            $comment->parent_id = $parentId;

            if ($comment->validate()) {
                $comment->save(false);

                // replies
                if (!empty($c['replies']) && is_array($c['replies'])) {
                    $this->saveCommentsRecursive($taskId, $c['replies'], (int) $comment->id, $fallbackAuthorId);
                }
            }
        }
    }

    /**
     * Повертає дерево коментарів у форматі, зручному фронту
     */
    private function serializeTaskCommentsTree(int $taskId): array
    {
        $roots = TaskComment::find()
            ->where(['task_id' => $taskId, 'parent_id' => null])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        $map = function (TaskComment $c) use (&$map) {
            return [
                'id' => (int) $c->id,
                'author' => $c->author_name ?: ($c->author ? ($c->author->name ?? ('ID ' . $c->author->id)) : null),
                'author_id' => $c->author_id,
                'text' => $c->text,
                'created_at' => (int) $c->created_at,
                'replies' => array_map($map, $c->replies),
            ];
        };

        return array_map($map, $roots);
    }

    /**
     * Мінімальна серіалізація задачі під фронт
     */
    private function serializeTask(Task $t): array
    {
        return [
            'id' => (int) $t->id,
            'title' => (string) $t->title,
            'status' => (string) $t->status,
            'planned_date' => (string) $t->planned_date,
            'type' => (string) $t->type,
            'expected_time' => (int) $t->expected_time,
            'actual_time' => (int) $t->actual_time,
            'expected_result' => (string) $t->expected_result,
            'result' => (string) ($t->result ?? ''),
            'description' => (string) $t->description,
            'manager' => (string) $t->manager,
            'executor_id' => $t->executor_id ? (int) $t->executor_id : null,
            'result_id' => $t->result_id ? (int) $t->result_id : null,
        ];
    }


    protected function notifyServer(Task $task): void
    {
        $url = Yii::$app->params['taskNotifyUrl'] ?? null;
        if (!$url) {
            return;
        }
        try {
            (new Client())->createRequest()
                ->setMethod('POST')
                ->setUrl($url)
                ->setData($task->toArray())
                ->send();
        } catch (\Throwable $e) {
            Yii::error('Failed to notify task creation: ' . $e->getMessage(), 'application');
        }
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
