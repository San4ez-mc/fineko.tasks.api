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
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
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
            // send all mails to a file by default.
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
            'enableStrictParsing' => false,
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['auth'],
                    'pluralize' => false,
                    'extraPatterns' => [
                        'POST login' => 'login',
                        'POST logout' => 'logout',
                        'POST telegram-login' => 'telegram-login',
                        'POST request-password-reset' => 'request-password-reset',
                        'POST reset-password' => 'reset-password',
                    ],
                ],

                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['task', 'result', 'user', 'position'],
                ],

                'GET task/by-date' => 'task/by-date',  // нове правило
                'GET test' => 'test/index',

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
        'as cors' => [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['https://tasks.fineko.space'], // ⚠️ має бути конкретний origin
                'Access-Control-Request-Method' => ['POST', 'GET', 'OPTIONS'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Allow-Headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
                'Access-Control-Max-Age' => 3600,
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
