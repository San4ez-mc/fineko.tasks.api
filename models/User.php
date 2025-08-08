<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName()
    {
        return 'user';
    }

    public function rules()
    {
        return [
            [['organization_id', 'username'], 'required'],
            [['organization_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['username', 'email', 'password_hash', 'auth_key', 'password_reset_token'], 'string', 'max' => 255],
            [['first_name', 'last_name', 'telegram_username'], 'string', 'max' => 100],
            [['telegram_id'], 'string', 'max' => 50],
            [['role'], 'string', 'max' => 50],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['password_reset_token'], 'unique'],
        ];
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['auth_key' => $token]);
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    public function getOrganization()
    {
        return $this->hasOne(Organization::class, ['id' => 'organization_id']);
    }

    public function getPositions()
    {
        return $this->hasMany(Position::class, ['id' => 'position_id'])
            ->viaTable('user_position', ['user_id' => 'id']);
    }

    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }
        return static::findOne(['password_reset_token' => $token]);
    }

    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = 3600;
        return $timestamp + $expire >= time();
    }

    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public function generateAccessToken($expire = 3600)
    {
        $this->access_token = Yii::$app->security->generateRandomString();
        $this->access_token_expire = time() + $expire;
        $this->save(false);
        return $this->access_token;
    }

    public function generateRefreshToken($expire = 2592000) // 30 днів
    {
        $this->refresh_token = Yii::$app->security->generateRandomString();
        $this->refresh_token_expire = time() + $expire;
        $this->save(false);
        return $this->refresh_token;
    }

}
