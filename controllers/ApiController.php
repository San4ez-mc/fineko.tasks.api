<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\Cors;
use yii\filters\auth\HttpBearerAuth;

/**
 * Базовий контролер для всіх API-контролерів.
 * Дає: CORS, Bearer auth, OPTIONS handler.
 */
abstract class piController extends Controller
{
    /**
     * Додаткові винятки з авторизації для конкретного контролера.
     * ДОДАВАЙ у дочірньому класі: public $authExcept = ['options', 'login', ...];
     */
    public $authExcept = ['options'];

    /**
     * Декларуємо дозволені методи. У дочірньому можна перевизначити або доповнити (merge з parent::verbs()).
     */
    public function verbs()
    {
        return [
            'options' => ['OPTIONS'],
        ];
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // CORS: глобальний фільтр у web.php вже є, але дублювання тут не шкодить і підстраховує
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
        ];

        // Bearer‑автентифікація з винятками (логін/відновлення тощо задаєш у дочірньому контролері)
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => $this->authExcept,
        ];

        return $behaviors;
    }

    /**
     * Універсальна відповідь на preflight
     */
    public function actionOptions()
    {
        return 'ok';
    }
}
