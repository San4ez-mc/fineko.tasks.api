<?php

namespace app\models;

use yii\db\ActiveRecord;

class Task extends ActiveRecord
{
    public static function tableName()
    {
        return 'task';
    }

    public function rules()
    {
        return [
            [['organization_id', 'title'], 'required'],
            [['organization_id', 'result_id', 'assigned_by', 'assigned_to', 'expected_time', 'actual_time', 'created_at', 'updated_at'], 'integer'],
            [['expected_result', 'result', 'comments'], 'string'],
            [['planned_date'], 'safe'],
            [['type', 'status'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 255],
        ];
    }
}
