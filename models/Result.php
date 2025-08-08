<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $organization_id
 * @property int|null $owner_id
 * @property int|null $parent_id
 * @property string $title
 * @property string|null $description
 * @property string|null $deadline      // DATETIME/DATE
 * @property string|null $completed_at  // DATETIME
 * @property string|null $created_at
 * @property string|null $updated_at
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
            [['organization_id', 'owner_id', 'parent_id'], 'integer'],
            [['description'], 'string'],
            [['deadline', 'completed_at', 'created_at', 'updated_at'], 'safe'],
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
            return (int) $this->getChildren()->count(); };
        $fields['tasks_total'] = function () {
            return (int) $this->getTasks()->count(); };
        $fields['tasks_done'] = function () {
            return (int) $this->getTasks()->andWhere(['status' => 'done'])->count(); };
        $fields['is_completed'] = function () {
            return $this->completed_at !== null; };

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
        // Якщо у твоєму проекті інша назва моделі — заміни app\models\Task на актуальну
        return $this->hasMany(Task::class, ['result_id' => 'id']);
    }

    public function beforeValidate()
    {
        if ($this->isNewRecord) {
            if (!$this->organization_id && !Yii::$app->user->isGuest) {
                /** @var User $user */
                $user = Yii::$app->user->identity;
                $this->organization_id = $user->organization_id ?? $this->organization_id;
            }
        }
        return parent::beforeValidate();
    }

    public function beforeSave($insert)
    {
        $now = date('Y-m-d H:i:s');
        if ($insert) {
            $this->created_at = $now;
        }
        $this->updated_at = $now;
        return parent::beforeSave($insert);
    }
}
