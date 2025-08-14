<?php
use yii\web\Response;
use yii\filters\Cors;

$db = require __DIR__ . '/db.php';

return [
    'id' => 'fineko-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            'enableCookieValidation' => false,
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'response' => [
            'format' => Response::FORMAT_JSON,
            'charset' => 'UTF-8',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                $origin = Yii::$app->request->headers->get('Origin');
                $allowed = [
                    'https://tasks.fineko.space',
                    'https://ftasks.local',
                    'http://ftasks.local',
                    'http://localhost:3000',
                ];
                if ($origin && in_array($origin, $allowed, true)) {
                    $headers = $response->headers;
                    $headers->set('Access-Control-Allow-Origin', $origin);
                    $headers->set('Access-Control-Allow-Credentials', 'true');
                    $headers->set('Vary', 'Origin');
                }
            },
        ],
        'user' => [
            'identityClass' => app\models\User::class,
            'enableAutoLogin' => false,
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'db' => $db,
        'log' => [
            // У dev покажемо максимум контексту
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'flushInterval' => 1,
            'targets' => [
                [
                    'class' => yii\log\FileTarget::class,
                    'logFile' => '@app/runtime/logs/app.log',
                    'levels' => ['error', 'warning', 'info'],
                    'categories' => [
                        'application',          // наші Yii::info/::error
                        'yii\db\Command::query',
                        'yii\db\Command::execute',
                        'yii\web\HttpException:*',
                    ],
                    'logVars' => [],          // не писати глобальні $_SERVER повністю
                    'exportInterval' => 1,    // писати одразу
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'rules' => [
                'POST auth/login' => 'auth/login',
                'POST auth/refresh' => 'auth/refresh',
                'POST auth/request-password-reset' => 'auth/request-password-reset',
                'POST auth/reset-password' => 'auth/reset-password',

                'GET users' => 'user/index',

                'GET results' => 'result/index',
                'POST results' => 'result/create',
                'GET results/<id:\d+>' => 'result/view',
                'PATCH results/<id:\d+>/toggle-done' => 'result/toggle-done',
                'PATCH results/<id:\d+>' => 'result/update',
                'DELETE results/<id:\d+>' => 'result/delete',
                'POST results/<id:\d+>/complete' => 'result/complete',

                'GET tasks/filter' => 'task/filter',
                'GET task/filter' => 'task/filter',
                'GET templates' => 'task/templates',
                'GET tasks/templates' => 'task/templates',
                'GET,POST tasks/daily' => 'task/daily',
                'GET tasks' => 'task/index',
                'GET tasks/<id:\d+>' => 'task/view',
                'POST tasks' => 'task/create',
                'PUT,PATCH tasks/<id:\d+>' => 'task/update',
                'DELETE tasks/<id:\d+>' => 'task/delete',

                // catch‑all preflight
                ['pattern' => '<path:.*>', 'route' => 'site/options', 'verb' => 'OPTIONS'],
            ],
        ],
    ],

    // Глобальний CORS (білий список)
    'as corsFilter' => [
        'class' => Cors::class,
        'cors' => [
            'Origin' => [
                'https://tasks.fineko.space',
                'https://ftasks.local',
                'http://ftasks.local',
            ],
            'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
            'Access-Control-Request-Headers' => ['Authorization', 'Content-Type', 'X-Requested-With'],
            'Access-Control-Allow-Credentials' => true,
            'Access-Control-Max-Age' => 86400,
            'Access-Control-Expose-Headers' => ['Content-Type'],
        ],
    ],

    // Всі відповіді JSON + лог запиту (без токена)
    'on beforeRequest' => function () {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $req = Yii::$app->request;
        $auth = $req->headers->get('Authorization', '');
        $masked = $auth ? (substr($auth, 0, 16) . '…') : '';
        Yii::info(sprintf(
            '[%s] %s %s | Origin=%s | Auth=%s',
            date('Y-m-d H:i:s'),
            $req->method,
            $req->url,
            $req->headers->get('Origin', '-'),
            $masked
        ), 'application');
    },

    'params' => [],
];
