<?php
namespace app\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use app\models\LoginForm;
use app\models\User;

class AuthController extends Controller
{
    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }


    public function actionLogin()
    {
        $model = new LoginForm();
        $body = Yii::$app->request->bodyParams;
        if (isset($body['login']) && !isset($body['username'])) {
            $body['username'] = $body['login'];
        }
        $model->load($body, '');
        if ($model->login()) {
            return ['success' => true, 'user' => Yii::$app->user->identity];
        }
        $errors = $model->getFirstErrors();
        $message = reset($errors) ?: 'Invalid credentials';
        return ['success' => false, 'message' => $message];
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return ['success' => true];
    }

    public function actionTelegramLogin()
    {
        $id = Yii::$app->request->bodyParams['telegram_id'] ?? null;
        if (!$id) {
            return ['success' => false, 'message' => 'telegram_id required'];
        }
        $user = \app\models\User::findOne(['telegram_id' => $id]);
        if ($user) {
            Yii::$app->user->login($user, 3600 * 24 * 30);
            return ['success' => true, 'user' => $user];
        }
        return ['success' => false, 'message' => 'User not found'];
    }


    public function actionRequestPasswordReset()
    {
        $email = Yii::$app->request->bodyParams['email'] ?? null;
        if (!$email) {
            return ['success' => false, 'message' => 'Email required'];
        }
        $user = User::findOne(['email' => $email]);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }
        $user->generatePasswordResetToken();
        if ($user->save(false)) {
            Yii::$app->mailer->compose('passwordResetToken', ['user' => $user])
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                ->setTo($user->email)
                ->setSubject('Password reset')
                ->send();
            return ['success' => true];
        }
        return ['success' => false, 'message' => 'Unable to generate token'];
    }

    public function actionResetPassword()
    {
        $token = Yii::$app->request->bodyParams['token'] ?? null;
        $password = Yii::$app->request->bodyParams['password'] ?? null;
        if (!$token || !$password) {
            return ['success' => false, 'message' => 'Token and password required'];
        }
        $user = User::findByPasswordResetToken($token);
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid or expired token'];
        }
        $user->setPassword($password);
        $user->removePasswordResetToken();
        if ($user->save(false)) {
            return ['success' => true];
        }
        return ['success' => false, 'message' => 'Unable to reset password'];
    }
}
