<?php

namespace App\Adapter\Http;

use yii\web\Controller;
use yii\web\Response;
use Yii;
use OpenApi\Generator;

class SwaggerController extends Controller
{
    public $layout = false;

    public function getViewPath(): string
    {
        return Yii::$app->basePath . '/src/Adapter/Http/views/swagger';
    }

    public function actionJson(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->headers->set('Content-Type', 'application/json');

        $path = Yii::$app->basePath . '/src/Adapter/Http';
        $openapi = Generator::scan([$path]);

        return $this->asJson(json_decode($openapi->toJson(), true));
    }

    public function actionIndex(): string
    {
        return $this->renderPartial('index');
    }
}
