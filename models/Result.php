<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $organization_id
 * @property string $title
 * @property string|null $description
 * @property string|null $expected_result
 * @property string|null $deadline        // DATE (Y-m-d)
 * @property int|null $created_by
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $parent_id
 * @property int|null $completed_at
 *
 * @property Result|null $parent
 * @property Result[] $children
 * @property Task[] $tasks
 */
class Result extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%result}}';
    }

    public function rules()
    {
        return [
            [['organization_id', 'title'], 'required'],
            [['organization_id', 'created_by', 'created_at', 'updated_at', 'parent_id', 'completed_at'], 'integer'],
            [['description', 'expected_result'], 'string'],
            [['deadline'], 'date', 'format' => 'php:Y-m-d'],
            [['title'], 'string', 'max' => 255],
            [['parent_id'], 'validateParent'],
        ];
    }

    public function validateParent($attribute)
    {
        if ($this->parent_id && (int) $this->parent_id === (int) $this->id) {
            $this->addError($attribute, 'parent_id не може дорівнювати id самого результату.');
        }
    }

    public function fields()
    {
        $fields = parent::fields();

        $fields['children_count'] = function () {
            return (int) $this->getChildren()->count();
        };
        $fields['tasks_total'] = function () {
            return (int) $this->getTasks()->count();
        };
        $fields['tasks_done'] = function () {
            return (int) $this->getTasks()->andWhere(['status' => 'done'])->count();
        };
        $fields['is_completed'] = function () {
            return $this->completed_at !== null;
        };

        return $fields;
    }

    public function getParent(): ActiveQuery
    {
        return $this->hasOne(Result::class, ['id' => 'parent_id']);
    }

    public function getChildren(): ActiveQuery
    {
        return $this->hasMany(Result::class, ['parent_id' => 'id']);
    }

    public function getTasks(): ActiveQuery
    {
        return $this->hasMany(Task::class, ['result_id' => 'id']);
    }

    public function beforeValidate()
    {
        if ($this->isNewRecord) {
            if (!$this->organization_id && !Yii::$app->user->isGuest) {
                $user = Yii::$app->user->identity;
                if (isset($user->organization_id)) {
                    $this->organization_id = (int) $user->organization_id;
                }
            }
            if (!$this->created_by && !Yii::$app->user->isGuest && isset(Yii::$app->user->id)) {
                $this->created_by = (int) Yii::$app->user->id;
            }
        }
        return parent::beforeValidate();
    }
    public function beforeSave($insert)
    {
        $now = time();
        if ($insert) {
            $this->created_at = $now;
        }
        $this->updated_at = $now;
        return parent::beforeSave($insert);
    }
}
