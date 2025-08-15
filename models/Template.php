<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Template extends ActiveRecord
{
    public static function tableName()
    {
        return 'template';
    }

    public function rules()
    {
        return [
            [['organization_id', 'name'], 'required'],
            [['organization_id', 'created_at', 'updated_at'], 'integer'],
            [['flags', 'payload'], 'safe'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    public function beforeValidate()
    {
        if ($this->isNewRecord && !$this->organization_id && !Yii::$app->user->isGuest && isset(Yii::$app->user->identity->organization_id)) {
            $this->organization_id = (int) Yii::$app->user->identity->organization_id;
        }
        return parent::beforeValidate();
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if (is_array($this->flags)) {
            $this->flags = json_encode($this->flags, JSON_UNESCAPED_UNICODE);
        }
        if (is_array($this->payload)) {
            $this->payload = json_encode($this->payload, JSON_UNESCAPED_UNICODE);
        }
        $now = time();
        if ($insert) {
            $this->created_at = $now;
        }
        $this->updated_at = $now;
        return true;
    }

    public function afterFind()
    {
        parent::afterFind();
        if (is_string($this->flags)) {
            $this->flags = json_decode($this->flags, true);
        }
        if (is_string($this->payload)) {
            $this->payload = json_decode($this->payload, true);
        }
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['flags'] = function () {
            return $this->flags;
        };
        $fields['payload'] = function () {
            return $this->payload;
        };
        return $fields;
    }
}
