<?php

namespace app\models;

use yii\db\ActiveRecord;

class Organization extends ActiveRecord
{
    public static function tableName()
    {
        return 'organization';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['comment'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }
}
