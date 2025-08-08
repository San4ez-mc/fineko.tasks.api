<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName()
    {
        return '{{%user}}';
    }

    /* ---------- IdentityInterface ---------- */

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::find()
            ->where(['access_token' => $token])
            ->andWhere(['>', 'access_token_expire', time()])
            ->one();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key ?? null;
    }

    public function validateAuthKey($authKey)
    {
        return $this->auth_key && $this->auth_key === $authKey;
    }

    /* ---------- Допоміжне ---------- */

    public static function findByUsername($username)
    {
        return static::find()->where(['username' => $username])->one();
    }

    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function generateAccessToken($expire = 3600)
    {
        $this->access_token = Yii::$app->security->generateRandomString(64);
        $this->access_token_expire = time() + (int) $expire;
        $this->save(false, ['access_token', 'access_token_expire']);
        return $this->access_token;
    }

    public function generateRefreshToken($expire = 2592000) // 30 днів
    {
        $this->refresh_token = Yii::$app->security->generateRandomString(64);
        $this->refresh_token_expire = time() + (int) $expire;
        $this->save(false, ['refresh_token', 'refresh_token_expire']);
        return $this->refresh_token;
    }
}
