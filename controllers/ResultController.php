<?php

namespace app\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use yii\web\Response;
use app\models\Result;

class ResultController extends ActiveController
{
    public $modelClass = 'app\models\Result';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        return $actions;
    }

    /**
     * Повертає список результатів з пагінацією, сортуванням та фільтрами
     * Виклик: GET /index.php?r=result/index&date=2025-07-24&type=normal&set_date=2025-07-20&creator=1&sort=asc&page=1&per-page=10
     */
    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;
        $date = $request->get('date');
        $setDate = $request->get('set_date');
        $creator = $request->get('creator');
        $type = $request->get('type');
        $sort = $request->get('sort', 'asc');
        $page = max((int)$request->get('page', 1), 1);
        $perPage = (int)$request->get('per-page', 20);

        $query = Result::find();

        if (!empty($date)) {
            $query->andWhere(['deadline' => $date]);
        }

        if (!empty($setDate)) {
            $start = strtotime($setDate . ' 00:00:00');
            $end = strtotime($setDate . ' 23:59:59');
            $query->andWhere(['between', 'created_at', $start, $end]);
        }

        if (!empty($creator)) {
            $query->andWhere(['created_by' => $creator]);
        }

        if (!empty($type)) {
            $query->andWhere(['type' => $type]);
        }

        $query->orderBy(['type' => $sort === 'desc' ? SORT_DESC : SORT_ASC]);

        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'page' => $page - 1,
                'pageSize' => $perPage,
            ],
        ]);

        return [
            'total_count' => $provider->getTotalCount(),
            'page' => $page,
            'per_page' => $perPage,
            'results' => array_map(fn($model) => $model->toArray(), $provider->getModels()),
        ];
    }
}
