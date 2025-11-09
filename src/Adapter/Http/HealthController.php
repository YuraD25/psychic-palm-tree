<?php

namespace App\Adapter\Http;

use Yii;
use yii\web\Controller;
use yii\web\Response;

class HealthController extends Controller
{
    public function actionIndex(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        return [
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'service' => 'loan-application-api',
            'endpoints' => [
                'POST /requests' => 'Submit loan application',
                'GET /processor' => 'Process pending loan requests',
                'GET /health' => 'Health check'
            ]
        ];
    }

    public function actionError(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $exception = Yii::$app->errorHandler->exception;
        
        if ($exception !== null) {
            Yii::$app->response->statusCode = $exception instanceof \yii\web\HttpException
                ? $exception->statusCode 
                : 500;
                
            return [
                'error' => true,
                'message' => $exception->getMessage(),
                'status' => Yii::$app->response->statusCode
            ];
        }
        
        Yii::$app->response->statusCode = 404;

        return [
            'error' => true,
            'message' => 'Page not found',
            'status' => 404
        ];
    }
}
