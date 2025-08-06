<?php

namespace app\controllers;

use yii\web\Controller;
use yii\web\Response;
use yii\filters\Cors;
use Yii;
use app\models\Task;

class TaskController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // ✅ Додаємо CORS, щоб фронтенд міг робити запити
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['https://ftasks.local', 'https://tasks.fineko.space'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 3600,
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];

        return $behaviors;
    }

    /**
     * ✅ 1. Отримати список задач по фільтрах
     * Виклик: GET /index.php?r=task/filter&date=2025-07-24&type=важлива&assigned_to=Іван&creator=Марія&sort=asc
     */
    public function actionFilter()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;
        $date = $request->get('date');
        $type = $request->get('type');
        $assignedTo = $request->get('assigned_to');
        $creator = $request->get('creator');
        $sort = $request->get('sort', 'asc'); // asc або desc

        if (!$date) {
            return [
                'error' => true,
                'message' => 'Дата є обов’язковим параметром'
            ];
        }

        $query = Task::find()->where(['planned_date' => $date]);

        if (!empty($type)) {
            $query->andWhere(['type' => $type]);
        }

        if (!empty($assignedTo)) {
            $query->andWhere(['assigned_to' => $assignedTo]);
        }

        if (!empty($creator)) {
            $query->andWhere(['creator' => $creator]);
        }

        $tasks = $query->orderBy(['deadline_date' => ($sort === 'desc' ? SORT_DESC : SORT_ASC)])
            ->asArray()
            ->all();

        return [
            'date' => $date,
            'count' => count($tasks),
            'tasks' => $tasks
        ];
    }

    /**
     * ✅ 2. Видалення задачі
     * DELETE /index.php?r=task/delete&id=123
     */
    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = Task::findOne($id);
        if (!$model) {
            return [
                'error' => true,
                'message' => 'Задачу не знайдено'
            ];
        }

        if ($model->delete()) {
            return [
                'success' => true,
                'message' => 'Задачу видалено'
            ];
        } else {
            return [
                'error' => true,
                'message' => 'Не вдалося видалити задачу'
            ];
        }
    }

    /**
     * ✅ 3. Редагування одного поля в задачі
     * PATCH /index.php?r=task/update-field&id=123
     * JSON: { "field": "title", "value": "Нова назва" }
     */
    public function actionUpdateField($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = Task::findOne($id);
        if (!$model) {
            return [
                'error' => true,
                'message' => 'Задачу не знайдено'
            ];
        }

        $body = Yii::$app->request->bodyParams;
        $field = $body['field'] ?? null;
        $value = $body['value'] ?? null;

        if (!$field || !array_key_exists($field, $model->attributes)) {
            return [
                'error' => true,
                'message' => 'Невірне поле для оновлення'
            ];
        }

        $model->$field = $value;

        if ($model->save(false)) { // false – щоб не валідувати все
            return [
                'success' => true,
                'message' => "Поле {$field} оновлено",
                'task' => $model->toArray()
            ];
        }

        return [
            'error' => true,
            'message' => 'Не вдалося оновити поле',
            'errors' => $model->getErrors()
        ];
    }
}
