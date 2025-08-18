<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $task_id
 * @property int|null $author_id
 * @property string|null $author_name
 * @property string $text
 * @property int|null $parent_id
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Task $task
 * @property User|null $author
 * @property TaskComment|null $parent
 * @property TaskComment[] $replies
 */
class TaskComment extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%task_comment}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules()
    {
        return [
            [['task_id', 'text'], 'required'],
            [['task_id', 'author_id', 'parent_id', 'created_at', 'updated_at'], 'integer'],
            [['text'], 'string'],
            [['author_name'], 'string', 'max' => 255],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => Task::class, 'targetAttribute' => ['task_id' => 'id']],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['author_id' => 'id']],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => TaskComment::class, 'targetAttribute' => ['parent_id' => 'id']],
        ];
    }

    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }

    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }

    public function getParent()
    {
        return $this->hasOne(TaskComment::class, ['id' => 'parent_id']);
    }

    public function getReplies()
    {
        return $this->hasMany(TaskComment::class, ['parent_id' => 'id'])->orderBy(['id' => SORT_ASC]);
    }
}
