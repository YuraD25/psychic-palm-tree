<?php

namespace App\Adapter\Http;

use App\Application\UseCase\ProcessLoanRequestsService;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\Cors;
use Yii;
use OpenApi\Attributes as OA;

class ProcessorController extends Controller
{
    private ProcessLoanRequestsService $processService;

    public function __construct($id, $module, ProcessLoanRequestsService $processService, $config = [])
    {
        $this->processService = $processService;
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['cors'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => false,
                'Access-Control-Max-Age' => 86400,
            ],
        ];

        return $behaviors;
    }

    #[OA\Get(
        path: '/processor',
        summary: 'Process pending loan applications',
        tags: ['Processor'],
        parameters: [
            new OA\Parameter(
                name: 'delay',
                in: 'query',
                description: 'Delay in seconds between processing requests (0-10)',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1, minimum: 0, maximum: 10)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Processing completed',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'result', type: 'boolean', example: true)
                    ]
                )
            )
        ]
    )]
    public function actionProcess(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->headers->set('Content-Type', 'application/json');

        $request = Yii::$app->request;

        $delay = (int)($request->get('delay') ?? 1);

        $delay = max(0, min(10, $delay));

        $result = $this->processService->process($delay);

        Yii::$app->response->statusCode = 200;

        return $this->asJson([
            'result' => $result
        ]);
    }
}
