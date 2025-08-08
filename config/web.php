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
        ],
        'user' => [
            'identityClass' => app\models\User::class,
            'enableAutoLogin' => false,
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'db' => $db,
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'POST auth/login' => 'auth/login',
                'POST auth/refresh' => 'auth/refresh',
                'POST auth/request-password-reset' => 'auth/request-password-reset',
                'POST auth/reset-password' => 'auth/reset-password',

                'GET results' => 'result/index',
                'POST results' => 'result/create',
                'GET results/<id:\d+>' => 'result/view',
                'PATCH results/<id:\d+>' => 'result/update',
                'DELETE results/<id:\d+>' => 'result/delete',
                'POST results/<id:\d+>/complete' => 'result/complete',

                // ◀️ Catch‑all для будь-якого preflight
                ['pattern' => '<path:.*>', 'route' => 'site/options', 'verb' => 'OPTIONS'],
            ],
        ],
    ],

    'as corsFilter' => [
        'class' => Cors::class,
        'cors' => [
            'Origin' => [
                'https://tasks.fineko.space',
                'http://ftasks.local',
                'http://localhost:3000',
            ],
            'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
            'Access-Control-Request-Headers' => ['Authorization', 'Content-Type', 'X-Requested-With'],
            'Access-Control-Allow-Credentials' => false,
            'Access-Control-Max-Age' => 86400,
            'Access-Control-Expose-Headers' => ['Content-Type'],
        ],
    ],

    'on beforeRequest' => function () {
        Yii::$app->response->format = Response::FORMAT_JSON;
    },

    'params' => [],
];
