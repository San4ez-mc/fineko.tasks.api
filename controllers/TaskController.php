<?php

namespace app\controllers;

use app\models\Task;
use app\models\TaskComment;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

class TaskController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'filter' => ['GET'],
                'update-field' => ['PATCH', 'POST'],
                'daily' => ['POST'],
            ],
        ];

        return $behaviors;
    }

    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    /**
     * GET /task/filter?date=YYYY-MM-DD&status=&priority=&timer=&result=
     */
    public function actionFilter()
    {
        if (Yii::$app->user->isGuest) {
            throw new UnauthorizedHttpException('Auth required');
        }

        $date = Yii::$app->request->get('date');
        $q = Task::find();
        if ($date) {
            // якщо у Task інша назва поля дати — заміни тут один раз
            $q->andWhere(['planned_date' => $date]);
        }

        $rows = $q->orderBy(['id' => SORT_DESC])->all();

        $tasks = array_map(function (Task $t) {
            $out = [
                'id' => (int) $t->id,
                'title' => (string) $t->title,
                'status' => (string) ($t->status ?? 'new'),
                'type' => (string) ($t->type ?? ''),
                'expected_time' => (int) ($t->expected_time ?? 0),
                'actual_time' => (int) ($t->actual_time ?? 0),
                'expected_result' => (string) ($t->expected_result ?? ''),
                'result' => (string) ($t->result ?? ''),
                'manager' => (string) ($t->manager ?? ''),
                'executor_id' => $t->executor_id ? (int) $t->executor_id : null,
                'result_id' => $t->result_id ? (int) $t->result_id : null,
                'result_title' => method_exists($t, 'getResult') && $t->result ? (string) $t->result->title : '',
                'comments' => [],
            ];

            // ці поля додаємо тільки якщо вони існують у схемі
            if ($t->hasAttribute('planned_date')) {
                $out['planned_date'] = (string) $t->planned_date;
            }
            if ($t->hasAttribute('description')) {
                $out['description'] = (string) $t->description;
            }
            // для сумісності з фронтом (він очікує .dueDate):
            $out['planned_date'] = $out['planned_date'] ?? '';
            $out['dueDate'] = $out['planned_date'];

            return $out;
        }, $rows);

        return ['tasks' => $tasks];
    }

    /**
     * PATCH /task/update-field?id=123 { field, value }
     */
    public function actionUpdateField($id)
    {
        if (Yii::$app->user->isGuest) {
            throw new UnauthorizedHttpException('Auth required');
        }

        $task = Task::findOne((int) $id);
        if (!$task) {
            throw new BadRequestHttpException('Task not found');
        }

        $body = Yii::$app->request->getBodyParams();
        $field = $body['field'] ?? null;
        if (!$field) {
            throw new BadRequestHttpException('Field is required');
        }

        // оновлюємо тільки дозволені/існуючі поля
        if (!$task->hasAttribute($field)) {
            return ['success' => false, 'message' => "Unknown field: {$field}"];
        }

        $value = $body['value'] ?? null;

        // якщо це comments — їх краще зберігати через окремий контролер task-comment (поки просто ігноруємо)
        if ($field === 'comments') {
            return ['success' => true]; // фронт одразу оновлює локальний стейт
        }

        $task->$field = $value;

        if (!$task->validate([$field])) {
            return ['success' => false, 'errors' => $task->getErrors()];
        }

        if (!$task->save(false, [$field])) {
            return ['success' => false, 'message' => 'Cannot save field'];
        }

        return ['success' => true];
    }

    /**
     * POST /task/daily
     * {
     *   planned_date, title, type, expected_time, actual_time, expected_result,
     *   description, manager, executor_id, result_id, comments:[...]|"json"
     * }
     */
    public function actionDaily()
    {
        if (Yii::$app->user->isGuest) {
            throw new UnauthorizedHttpException('Auth required');
        }

        $data = Yii::$app->request->getBodyParams();

        // розпарсити comments: рядок JSON чи масив
        $commentsRaw = $data['comments'] ?? '[]';
        if (is_string($commentsRaw)) {
            try {
                $comments = json_decode($commentsRaw, true, 512, JSON_THROW_ON_ERROR);
            } catch (\Throwable $e) {
                $comments = [];
                Yii::warning('Invalid comments JSON: ' . $e->getMessage(), __METHOD__);
            }
        } else {
            $comments = is_array($commentsRaw) ? $commentsRaw : [];
        }

        $t = Yii::$app->db->beginTransaction();
        try {
            $task = new Task();

            // безпечне присвоєння тільки існуючих атрибутів
            $this->setIfHas($task, 'title', trim((string) ($data['title'] ?? '')));
            $this->setIfHas($task, 'type', (string) ($data['type'] ?? 'неважлива нетермінова'));
            $this->setIfHas($task, 'planned_date', (string) ($data['planned_date'] ?? null));
            $this->setIfHas($task, 'expected_time', (int) ($data['expected_time'] ?? 0));
            $this->setIfHas($task, 'actual_time', (int) ($data['actual_time'] ?? 0));
            $this->setIfHas($task, 'expected_result', (string) ($data['expected_result'] ?? ''));
            $this->setIfHas($task, 'manager', (string) ($data['manager'] ?? ''));
            $this->setIfHas($task, 'executor_id', !empty($data['executor_id']) ? (int) $data['executor_id'] : null);
            $this->setIfHas($task, 'result_id', !empty($data['result_id']) ? (int) $data['result_id'] : null);

            // поле description є не завжди — ставимо лише якщо воно існує
            if ($task->hasAttribute('description')) {
                $task->description = (string) ($data['description'] ?? '');
            }

            // дефолтний статус
            if ($task->hasAttribute('status') && empty($task->status)) {
                $task->status = 'new';
            }

            if (!$task->validate()) {
                $t->rollBack();
                return ['success' => false, 'errors' => $task->getErrors()];
            }

            if (!$task->save(false)) {
                $t->rollBack();
                throw new BadRequestHttpException('Cannot save task');
            }

            // коментарі — тільки якщо таблиця існує
            $commentsTableExists = Yii::$app->db->schema->getTableSchema('{{%task_comment}}', true) !== null;
            if ($commentsTableExists && !empty($comments)) {
                $this->saveCommentsRecursive((int) $task->id, $comments, null, (int) Yii::$app->user->id);
            } elseif (!$commentsTableExists && !empty($comments)) {
                Yii::warning('Comments were provided but task_comment table does not exist', __METHOD__);
            }

            $t->commit();

            return [
                'success' => true,
                'task' => $this->serializeTask($task),
                'comments' => $commentsTableExists ? $this->serializeTaskCommentsTree((int) $task->id) : [],
            ];
        } catch (\Throwable $e) {
            $t->rollBack();
            Yii::error('POST /task/daily failed: ' . $e->getMessage(), __METHOD__);
            if (defined('YII_DEBUG') && YII_DEBUG) {
                return ['success' => false, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()];
            }
            return ['success' => false, 'message' => 'Internal error'];
        }
    }

    // ===================== helpers =====================

    private function setIfHas(Task $task, string $attr, $value): void
    {
        if ($task->hasAttribute($attr)) {
            $task->$attr = $value;
        }
    }

    private function saveCommentsRecursive(int $taskId, array $comments, ?int $parentId, ?int $fallbackAuthorId): void
    {
        foreach ($comments as $c) {
            if (!isset($c['text']) || $c['text'] === '') {
                continue;
            }
            $comment = new TaskComment();
            $comment->task_id = $taskId;
            $comment->author_id = $fallbackAuthorId ?: null;
            $comment->author_name = isset($c['author']) && is_string($c['author']) ? $c['author'] : null;
            $comment->text = (string) $c['text'];
            $comment->parent_id = $parentId;

            if ($comment->validate() && $comment->save(false)) {
                if (!empty($c['replies']) && is_array($c['replies'])) {
                    $this->saveCommentsRecursive($taskId, $c['replies'], (int) $comment->id, $fallbackAuthorId);
                }
            } else {
                Yii::warning(['commentSaveError' => $comment->getErrors()], __METHOD__);
            }
        }
    }

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

    private function serializeTask(Task $t): array
    {
        $out = [
            'id' => (int) $t->id,
            'title' => (string) $t->title,
            'status' => (string) ($t->status ?? 'new'),
            'type' => (string) ($t->type ?? ''),
            'expected_time' => (int) ($t->expected_time ?? 0),
            'actual_time' => (int) ($t->actual_time ?? 0),
            'expected_result' => (string) ($t->expected_result ?? ''),
            'result' => (string) ($t->result ?? ''),
            'manager' => (string) ($t->manager ?? ''),
            'executor_id' => $t->executor_id ? (int) $t->executor_id : null,
            'result_id' => $t->result_id ? (int) $t->result_id : null,
            'result_title' => method_exists($t, 'getResult') && $t->result ? (string) $t->result->title : '',
        ];

        if ($t->hasAttribute('planned_date')) {
            $out['planned_date'] = (string) $t->planned_date;
            $out['dueDate'] = $out['planned_date']; // для фронта
        }
        if ($t->hasAttribute('description')) {
            $out['description'] = (string) $t->description;
        }

        return $out;
    }
}
