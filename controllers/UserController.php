<?php
namespace app\controllers;

use app\models\User;
use Yii;
use yii\filters\VerbFilter;

class UserController extends ApiController
{
    public function behaviors()
    {
        $b = parent::behaviors();
        $b['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index' => ['GET'],
            ],
        ];

        return $b;
    }

    public function actionIndex()
    {
        $active = Yii::$app->request->get('active');
        $query = User::find()->select(['id', 'first_name', 'last_name', 'username']);

        if ($active === '1' && User::getTableSchema()->getColumn('status')) {
            $query->andWhere(['status' => 10]);
        }

        $fetch = function () use ($query) {
            $rows = $query->orderBy(['last_name' => SORT_ASC, 'first_name' => SORT_ASC, 'id' => SORT_ASC])->asArray()->all();
            return array_map(function ($u) {
                return [
                    'id' => (int) $u['id'],
                    'first_name' => $u['first_name'],
                    'last_name' => $u['last_name'],
                    'username' => $u['username'],
                ];
            }, $rows);
        };

        if (Yii::$app->has('cache')) {
            $key = 'users_' . ($active === '1' ? 'active' : 'all');
            return Yii::$app->cache->getOrSet($key, $fetch, 60);
        }

        return $fetch();
    }
}
