<?php

namespace App\Adapter\Http;

use App\Application\UseCase\SubmitLoanRequestService;
use App\Infrastructure\Validation\ValidationBehavior;
use App\Infrastructure\Validation\Validator\SubmitLoanRequestValidator;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\Cors;
use Yii;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'RESTful API for processing loan applications with automated decision making',
    title: 'Loan Application API'
)]
#[OA\Server(url: 'http://localhost', description: 'Local development server')]

class LoanRequestController extends Controller
{
    private SubmitLoanRequestService $submitService;
    public mixed $validatedData = null;

    public function __construct($id, $module, SubmitLoanRequestService $submitService, $config = [])
    {
        $this->submitService = $submitService;
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['cors'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['POST', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => false,
                'Access-Control-Max-Age' => 86400,
            ],
        ];

        $behaviors['validation'] = [
            'class' => ValidationBehavior::class,
            'validators' => [
                'create' => SubmitLoanRequestValidator::class
            ]
        ];

        return $behaviors;
    }

    #[OA\Post(
        path: '/requests',
        summary: 'Submit a new loan application',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['user_id', 'amount', 'term'],
                properties: [
                    new OA\Property(property: 'user_id', type: 'integer', example: 1),
                    new OA\Property(property: 'amount', type: 'number', format: 'float', example: 10000.00),
                    new OA\Property(property: 'term', type: 'integer', example: 12)
                ]
            )
        ),
        tags: ['Loan Requests'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Loan request created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'result', type: 'boolean', example: true),
                        new OA\Property(property: 'id', type: 'integer', example: 1)
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request or user not eligible',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'result', type: 'boolean', example: false)
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'errors', type: 'object')
                    ]
                )
            )
        ]
    )]
    public function actionCreate(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->headers->set('Content-Type', 'application/json');

        $dto = $this->validatedData;

        $requestId = $this->submitService->submit($dto->userId, $dto->amount, $dto->term);

        if ($requestId === null) {
            Yii::$app->response->statusCode = 400;

            return $this->asJson(['result' => false]);
        }

        Yii::$app->response->statusCode = 201;

        return $this->asJson([
            'result' => true,
            'id' => $requestId
        ]);
    }
}
