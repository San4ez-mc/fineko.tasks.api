<?php
namespace app\controllers;

use Yii;
use yii\rest\Controller;
use yii\filters\Cors;
use yii\filters\auth\HttpBearerAuth;

/**
 * Базовий API-контролер: CORS, Bearer-автентифікація, OPTIONS 200.
 * Усі інші контролери успадковуються від нього.
 */
class ApiController extends Controller
{
    /**
     * Які дії НЕ потребують авторизації (дочірні контролери можуть перевизначити).
     */
    protected function authExcept(): array
    {
        // Preflight має проходити завжди
        return ['options'];
    }

    /**
     * Дозволені методи за замовчуванням.
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

        // 1) CORS — перед автентифікацією
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                // ВАЖЛИВО: локальний домен без https
                'Origin' => [
                    'https://tasks.fineko.space',
                    'http://ftasks.local',
                    // якщо треба dev CRA:
                    // 'http://localhost:3000',
                ],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['Authorization', 'Content-Type', 'X-Requested-With'],
                // Ми не використовуємо cookies → креденшали не потрібні
                'Access-Control-Allow-Credentials' => false,
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Expose-Headers' => ['Content-Type'],
            ],
        ];

        // 2) Bearer auth — після CORS, з винятками
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => $this->authExcept(),
        ];

        return $behaviors;
    }

    /**
     * Гарантований 200 OK на preflight (без заходу в екшени/автентифікацію).
     */
    public function beforeAction($action)
    {
        if (Yii::$app->request->isOptions) {
            Yii::$app->response->statusCode = 200;
            // Додатково виставимо ACAO під конкретний Origin (на випадок, якщо сервер не прокинув)
            $origin = Yii::$app->request->headers->get('Origin');
            $allowed = ['https://tasks.fineko.space', 'http://ftasks.local'];
            if ($origin && in_array($origin, $allowed, true)) {
                $h = Yii::$app->response->headers;
                $h->set('Access-Control-Allow-Origin', $origin);
                $h->set('Vary', 'Origin');
                $h->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
                $h->set('Access-Control-Allow-Headers', 'Authorization, Content-Type, X-Requested-With');
                // Креденшали не виставляємо, бо вони вимкнені
            }
            return false;
        }
        return parent::beforeAction($action);
    }

    /**
     * Handler для /.../options (на випадок прямого виклику)
     */
    public function actions()
    {
        return [
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
        ];
    }
}
