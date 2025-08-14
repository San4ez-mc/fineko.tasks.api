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
 * @property int $urgent
 * @property int|null $assigned_to
 * @property int|null $setter_id
 * @property string|null $date           // DATE (Y-m-d)
 * @property string|null $due_date       // DATETIME (Y-m-d H:i:s)
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
            [['organization_id', 'title', 'expected_result', 'assigned_to'], 'required'],
            [['organization_id', 'created_by', 'created_at', 'updated_at', 'parent_id', 'completed_at', 'assigned_to', 'setter_id'], 'integer'],
            [['description', 'expected_result'], 'string'],
            [['date'], 'date', 'format' => 'php:d.m.Y'],
            [['title'], 'string', 'max' => 255],
            [['urgent'], 'boolean'],
            [['urgent'], 'default', 'value' => 0],
            [['urgent'], 'integer', 'min' => 0, 'max' => 1],
            [['due_date', 'date'], 'safe'],
            [['due_date'], 'validateNotPast'],
            [['date'], 'validateNotPast'],
            [['assigned_to'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['assigned_to' => 'id'], 'message' => 'Відповідальний користувач не знайдений.'],
            [['parent_id'], 'validateParent'],
        ];
    }

    public function validateParent($attribute)
    {
        if ($this->parent_id && (int) $this->parent_id === (int) $this->id) {
            $this->addError($attribute, 'parent_id не може дорівнювати id самого результату.');
        }
    }

    public function validateNotPast($attribute)
    {
        if (empty($this->$attribute)) {
            return;
        }
        $isDueDate = $attribute === 'due_date';
        $format = $isDueDate ? 'd.m.Y H:i' : 'd.m.Y';
        $dt = \DateTime::createFromFormat($format, $this->$attribute);
        $now = $isDueDate ? new \DateTime() : new \DateTime('today');
        if ($dt && $dt < $now) {
            $this->addError($attribute, 'Дата не може бути в минулому.');
        }
    }

    public function fields()
    {
        $fields = parent::fields();

        $fields['final_result'] = function () {
            return $this->expected_result;
        };
        $fields['responsible_id'] = function () {
            return $this->assigned_to;
        };
        $fields['urgent'] = function () {
            return (bool) $this->urgent;
        };
        $fields['due_date'] = function ($model) {
            if (empty($model->due_date)) {
                return null;
            }
            $dt = new \DateTime($model->due_date);
            return $dt->format('d.m.Y H:i');
        };
        $fields['date'] = function ($model) {
            if (empty($model->date)) {
                return null;
            }
            $dt = new \DateTime($model->date);
            return $dt->format('d.m.Y');
        };

        unset($fields['expected_result'], $fields['assigned_to']);

        $fields['children_count'] = function () {
            return (int) $this->getChildren()->count();
        };
        $fields['tasks_total'] = function () {
            return (int) $this->getTasks()->count();
        };
        $fields['tasks_done'] = function () {
            return (int) $this->getTasks()->andWhere(['status' => 'done'])->count();
        };
        $fields['completed_at'] = function () {
            return $this->completed_at ? gmdate('c', $this->completed_at) : null;
        };
        $fields['is_completed'] = function () {
            return $this->completed_at !== null;
        };
        $fields['is_done'] = function () {
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

    public function getAssignee(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'assigned_to']);
    }

    public function getSetter(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'setter_id']);
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
            if (!$this->setter_id && !Yii::$app->user->isGuest && isset(Yii::$app->user->id)) {
                $this->setter_id = (int) Yii::$app->user->id;
            }
        }
        return parent::beforeValidate();
    }
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (!empty($this->due_date)) {
            $dt = \DateTime::createFromFormat('d.m.Y H:i', $this->due_date);
            if ($dt) {
                $this->due_date = $dt->format('Y-m-d H:i:s');
            }
        }
        if (!empty($this->date)) {
            $dt = \DateTime::createFromFormat('d.m.Y', $this->date);
            if ($dt) {
                $this->date = $dt->format('Y-m-d');
            }
        }

        $now = time();
        if ($insert) {
            $this->created_at = $now;
        }
        $this->updated_at = $now;

        return true;
    }
}
