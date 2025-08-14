<?php

namespace app\models\search;

use app\models\Result;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class ResultSearch extends Model
{
    public $q;
    public $status;     // 'active' | 'done' | null
    public $dueFrom;
    public $dueTo;
    public $parent_id;

    public function rules()
    {
        return [
            [['q', 'status'], 'string'],
            [['dueFrom', 'dueTo'], 'safe'],
            [['parent_id'], 'integer'],
        ];
    }

    public function search(array $params = []): ActiveDataProvider
    {
        $query = Result::find();

        // фільтруємо за організацією поточного користувача
        if (!Yii::$app->user->isGuest && property_exists(Yii::$app->user->identity, 'organization_id')) {
            $query->andWhere(['organization_id' => Yii::$app->user->identity->organization_id]);
        }

        $this->load($params, '');

        if ($this->q) {
            $query->andFilterWhere([
                'or',
                ['like', 'title', $this->q],
                ['like', 'description', $this->q],
            ]);
        }

        if ($this->status === 'done') {
            $query->andWhere(['IS NOT', 'completed_at', null]);
        } elseif ($this->status === 'active') {
            $query->andWhere(['completed_at' => null]);
        }

        if ($this->parent_id !== null) {
            $query->andWhere(['parent_id' => $this->parent_id]);
        }

        if ($this->dueFrom) {
            $query->andWhere(['>=', 'date', $this->dueFrom]);
        }
        if ($this->dueTo) {
            $query->andWhere(['<=', 'date', $this->dueTo]);
        }

        $query->orderBy(['date' => SORT_ASC, 'id' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 25,
                'pageParam' => 'page',
                'pageSizeParam' => 'per-page',
            ],
        ]);

        return $dataProvider;
    }
}
