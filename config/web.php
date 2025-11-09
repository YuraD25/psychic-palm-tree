<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$di = require __DIR__ . '/di.php';

$config = [
    'id' => 'loan-application-api',
    'name' => 'Loan Application API',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'container' => $di,
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'loan-app-secret-key-change-in-production',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'response' => [
            'formatters' => [
                \yii\web\Response::FORMAT_JSON => [
                    'class' => 'yii\web\JsonResponseFormatter',
                    'prettyPrint' => YII_DEBUG,
                    'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                ],
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
            'enableSession' => false,
        ],
        'errorHandler' => [
            'errorAction' => 'health/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
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
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'loan-request',
                    'patterns' => [
                        'POST' => 'create',
                    ],
                    'pluralize' => false,
                ],
                [
                    'class' => 'yii\rest\UrlRule', 
                    'controller' => 'processor',
                    'patterns' => [
                        'GET' => 'process',
                    ],
                    'pluralize' => false,
                ],
                // Default route for root path
                '' => 'health/index',
                // Swagger documentation
                'GET swagger' => 'swagger/index',
                'GET swagger/json' => 'swagger/json',
                // Fallback rules for exact endpoint matching
                'POST requests' => 'loan-request/create',
                'GET processor' => 'processor/process',
                'GET health' => 'health/index',
            ],
        ],
    ],
    'controllerMap' => [
        'loan-request' => [
            'class' => 'App\Adapter\Http\LoanRequestController',
        ],
        'processor' => [
            'class' => 'App\Adapter\Http\ProcessorController',
        ],
        'health' => 'App\Adapter\Http\HealthController',
        'swagger' => 'App\Adapter\Http\SwaggerController',
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['127.0.0.1', '::1', '172.*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', '::1', '172.*'],
    ];
}

return $config;