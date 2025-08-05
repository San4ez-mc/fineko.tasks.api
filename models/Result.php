<?php

namespace app\models;

use yii\db\ActiveRecord;

class Result extends ActiveRecord
{
    public static function tableName()
    {
        return 'result';
    }

    public function rules()
    {
        return [
            [['organization_id', 'title'], 'required'],
            [['organization_id', 'created_by', 'created_at', 'updated_at'], 'integer'],
            [['description', 'expected_result'], 'string'],
            [['deadline'], 'safe'],
            [['title'], 'string', 'max' => 255],
        ];
    }
}
