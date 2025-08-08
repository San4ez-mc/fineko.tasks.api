<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],

    // ✅ CORS на верхньому рівні
    'as cors' => [
        'class' => \yii\filters\Cors::class,
        'cors' => [
            'Origin' => ['https://tasks.fineko.space'],
            'Access-Control-Request-Method' => ['POST', 'GET', 'OPTIONS'],
            'Access-Control-Allow-Credentials' => true,
            'Access-Control-Allow-Headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
            'Access-Control-Max-Age' => 3600,
        ],
    ],

    'components' => [
        'request' => [
            'cookieValidationKey' => 'RnmH64wG8bRABrW3rP9QUI0kEDBdcCJz',
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'error/index',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // Auth
                'POST auth/login' => 'auth/login',

                // Results CRUD
                'GET results' => 'result/index',
                'POST results' => 'result/create',
                'GET results/<id:\d+>' => 'result/view',
                'PUT results/<id:\d+>' => 'result/update',
                'PATCH results/<id:\d+>' => 'result/update',
                'DELETE results/<id:\d+>' => 'result/delete',

                // Toggle complete (дозволяємо і PATCH, і POST)
                'PATCH results/<id:\d+>/complete' => 'result/complete',
                'POST  results/<id:\d+>/complete' => 'result/complete',
            ],
        ],
        'response' => [
            'format' => yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
            'formatters' => [
                yii\web\Response::FORMAT_JSON => [
                    'class' => yii\web\JsonResponseFormatter::class,
                    'encodeOptions' => JSON_UNESCAPED_UNICODE,
                ],
            ],
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                $origin = \Yii::$app->request->headers->get('Origin');
                if ($origin) {
                    $response->headers->set('Access-Control-Allow-Origin', $origin);
                } else {
                    $response->headers->set('Access-Control-Allow-Origin', '*');
                }
                $response->headers->set('Access-Control-Allow-Credentials', 'true');
                $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
            },
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
