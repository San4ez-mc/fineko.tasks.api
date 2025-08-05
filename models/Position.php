<?php
namespace app\models;

use yii\db\ActiveRecord;

class Position extends ActiveRecord
{
    public static function tableName()
    {
        return 'position';
    }

    public function rules()
    {
        return [
            [['organization_id', 'name'], 'required'],
            [['organization_id', 'manager_id', 'created_at', 'updated_at'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    public function getManager()
    {
        return $this->hasOne(self::class, ['id' => 'manager_id']);
    }

    public function getSubordinates()
    {
        return $this->hasMany(self::class, ['manager_id' => 'id']);
    }

    public function getOrganization()
    {
        return $this->hasOne(Organization::class, ['id' => 'organization_id']);
    }

    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('user_position', ['position_id' => 'id']);
    }
}
