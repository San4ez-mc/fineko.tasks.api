<?php

namespace app\models;

use yii\db\ActiveRecord;

class BusinessProcess extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%business_process}}';
    }

    public function rules()
    {
        return [
            [['name', 'schema'], 'required'],
            [['schema'], 'safe'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['schema'] = function ($model) {
            if (is_string($model->schema)) {
                $decoded = json_decode($model->schema, true);
                return $decoded === null ? $model->schema : $decoded;
            }
            return $model->schema;
        };
        return $fields;
    }

    public function beforeSave($insert)
    {
        if (is_array($this->schema)) {
            $this->schema = json_encode($this->schema);
        }
        $now = time();
        if ($insert) {
            $this->created_at = $now;
        }
        $this->updated_at = $now;
        return parent::beforeSave($insert);
    }
}
