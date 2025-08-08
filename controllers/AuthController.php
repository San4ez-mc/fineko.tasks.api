<?php
namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;
use app\models\User;

/**
 * Авторизація через Bearer токени (без сесій).
 * Залишено твої методи reset/telegram, але login переписано під токени.
 */
class AuthController extends ApiController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        // ✅ login/refresh/telegram/reset — доступні без токена
        if (isset($behaviors['authenticator'])) {
            $behaviors['authenticator']['except'] = [
                'login',
                'refresh',
                'request-password-reset',
                'reset-password',
                'telegram-login',
                'options'
            ];
        }
        return $behaviors;
    }

    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    /**
     * POST /auth/login
     * body: { "username" | "login": "...", "password": "..." }
     * Повертає: access_token (1 год), refresh_token (30 днів)
     */
    public function actionLogin()
    {
        $body = Yii::$app->request->bodyParams;

        // Сумісність з попереднім фронтом (login -> username)
        if (isset($body['login']) && !isset($body['username'])) {
            $body['username'] = $body['login'];
        }

        $username = $body['username'] ?? null;
        $password = $body['password'] ?? null;

        if (!$username || !$password) {
            throw new BadRequestHttpException('Username and password are required.');
        }

        $user = User::findByUsername($username);
        if (!$user || !$user->validatePassword($password)) {
            throw new UnauthorizedHttpException('Invalid credentials.');
        }

        $access = $user->generateAccessToken(3600);      // 1 година
        $refresh = $user->generateRefreshToken(2592000);  // 30 днів

        return [
            'success' => true,
            'access_token' => $access,
            'refresh_token' => $refresh,
            'expires_in' => 3600,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email ?? null,
            ],
        ];
    }

    /**
     * POST /auth/refresh
     * body: { "refresh_token": "..." }
     * Повертає новий access_token
     */
    public function actionRefresh()
    {
        $body = Yii::$app->request->bodyParams;
        $refreshToken = $body['refresh_token'] ?? null;

        if (!$refreshToken) {
            throw new BadRequestHttpException('refresh_token is required.');
        }

        $user = User::find()
            ->where(['refresh_token' => $refreshToken])
            ->andWhere(['>', 'refresh_token_expire', time()])
            ->one();

        if (!$user) {
            throw new UnauthorizedHttpException('Invalid refresh token.');
        }

        $access = $user->generateAccessToken(3600); // 1 година

        return [
            'success' => true,
            'access_token' => $access,
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * ЗАЛИШЕНО з твого файлу (працює без сесій).
     * Якщо хочеш — можемо перевести і тут на токени (телеграм-логін повертатиме токени).
     */
    public function actionTelegramLogin()
    {
        $id = Yii::$app->request->bodyParams['telegram_id'] ?? null;
        if (!$id) {
            return ['success' => false, 'message' => 'telegram_id required'];
        }
        $user = \app\models\User::findOne(['telegram_id' => $id]);
        if ($user) {
            // ⚠️ У тебе тут був login через сесію — ми API робимо без сесій.
            // Якщо треба, можемо теж повертати токени:
            $access = $user->generateAccessToken(3600);
            $refresh = $user->generateRefreshToken(2592000);
            return [
                'success' => true,
                'access_token' => $access,
                'refresh_token' => $refresh,
                'expires_in' => 3600,
                'token_type' => 'Bearer',
                'user' => $user,
            ];
        }
        return ['success' => false, 'message' => 'User not found'];
    }

    /** ЗАЛИШЕНО: запит reset-посилання на пошту */
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

    /** ЗАЛИШЕНО: встановлення нового пароля по токену */
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
