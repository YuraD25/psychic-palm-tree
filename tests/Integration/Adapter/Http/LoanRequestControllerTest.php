<?php

namespace Tests\Integration\Adapter\Http;

use App\Adapter\Http\LoanRequestController;
use App\Application\UseCase\SubmitLoanRequestService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class LoanRequestControllerTest extends TestCase
{
    private $app;
    private MockObject $submitServiceMock;
    private LoanRequestController $controller;

    protected function setUp(): void
    {
        $this->submitServiceMock = $this->createMock(SubmitLoanRequestService::class);
        
        $config = [
            'id' => 'test-app',
            'basePath' => dirname(__DIR__, 3),
            'components' => [
                'request' => [
                    'class' => 'yii\web\Request',
                    'cookieValidationKey' => 'test-key',
                    'parsers' => [
                        'application/json' => 'yii\web\JsonParser',
                    ],
                    'enableCsrfValidation' => false,
                ],
                'response' => [
                    'class' => 'yii\web\Response',
                    'format' => \yii\web\Response::FORMAT_JSON,
                ],
                'user' => [
                    'class' => 'yii\web\User',
                    'identityClass' => 'yii\web\IdentityInterface',
                    'enableSession' => false,
                ],
            ],
            'container' => [
                'singletons' => [
                    SubmitLoanRequestService::class => function() {
                        return $this->submitServiceMock;
                    }
                ]
            ]
        ];
        
        $this->app = new \yii\web\Application($config);
        $this->controller = new LoanRequestController('loan-request', $this->app, $this->submitServiceMock);
    }

    protected function tearDown(): void
    {
        \Yii::$app = null;
        $this->app = null;
    }

    public function testActionCreateWithValidDataReturnsSuccess(): void
    {
        $expectedId = 42;
        
        $this->submitServiceMock
            ->expects($this->once())
            ->method('submit')
            ->with(1, 5000, 12)
            ->willReturn($expectedId);
        
        $postData = [
            'user_id' => 1,
            'amount' => 5000,
            'term' => 12
        ];
        
        \Yii::$app->request->setBodyParams($postData);
        
        $action = new \yii\base\Action('create', $this->controller);
        $this->controller->beforeAction($action);
        
        $response = $this->controller->actionCreate();
        
        $this->assertInstanceOf(\yii\web\Response::class, $response);
        $this->assertEquals(201, \Yii::$app->response->statusCode);
        
        $data = $response->data;
        $this->assertTrue($data['result']);
        $this->assertEquals($expectedId, $data['id']);
    }

    public function testActionCreateWithInvalidUserIdReturns400(): void
    {
        $this->submitServiceMock
            ->expects($this->never())
            ->method('submit');
        
        $_POST = [
            'user_id' => 'invalid',
            'amount' => 5000,
            'term' => 12
        ];
        
        \Yii::$app->request->setBodyParams($_POST);
        
        $this->controller->beforeAction(new \yii\base\Action('create', $this->controller));
        
        $this->assertEquals(400, \Yii::$app->response->statusCode);
        
        $data = \Yii::$app->response->data;
        $this->assertFalse($data['result']);
        $this->assertArrayHasKey('errors', $data);
        $this->assertArrayHasKey('user_id', $data['errors']);
    }

    public function testActionCreateWithInvalidAmountReturns400(): void
    {
        $this->submitServiceMock
            ->expects($this->never())
            ->method('submit');
        
        $_POST = [
            'user_id' => 1,
            'amount' => 'invalid',
            'term' => 12
        ];
        
        \Yii::$app->request->setBodyParams($_POST);
        
        $this->controller->beforeAction(new \yii\base\Action('create', $this->controller));
        
        $this->assertEquals(400, \Yii::$app->response->statusCode);
        
        $data = \Yii::$app->response->data;
        $this->assertFalse($data['result']);
        $this->assertArrayHasKey('errors', $data);
        $this->assertArrayHasKey('amount', $data['errors']);
    }

    public function testActionCreateWithInvalidTermReturns400(): void
    {
        $this->submitServiceMock
            ->expects($this->never())
            ->method('submit');
        
        $_POST = [
            'user_id' => 1,
            'amount' => 5000,
            'term' => 'invalid'
        ];
        
        \Yii::$app->request->setBodyParams($_POST);
        
        $this->controller->beforeAction(new \yii\base\Action('create', $this->controller));
        
        $this->assertEquals(400, \Yii::$app->response->statusCode);
        
        $data = \Yii::$app->response->data;
        $this->assertFalse($data['result']);
        $this->assertArrayHasKey('errors', $data);
        $this->assertArrayHasKey('term', $data['errors']);
    }

    public function testActionCreateWithMissingFieldsReturns400(): void
    {
        $this->submitServiceMock
            ->expects($this->never())
            ->method('submit');
        
        $_POST = [];
        
        \Yii::$app->request->setBodyParams($_POST);
        
        $this->controller->beforeAction(new \yii\base\Action('create', $this->controller));
        
        $this->assertEquals(400, \Yii::$app->response->statusCode);
        
        $data = \Yii::$app->response->data;
        $this->assertFalse($data['result']);
        $this->assertArrayHasKey('errors', $data);
        $this->assertArrayHasKey('user_id', $data['errors']);
        $this->assertArrayHasKey('amount', $data['errors']);
        $this->assertArrayHasKey('term', $data['errors']);
    }

    public function testActionCreateWithMultipleValidationErrorsReturnsAllErrors(): void
    {
        $this->submitServiceMock
            ->expects($this->never())
            ->method('submit');
        
        $_POST = [
            'user_id' => -1,
            'amount' => 0,
            'term' => 'invalid'
        ];
        
        \Yii::$app->request->setBodyParams($_POST);
        
        $this->controller->beforeAction(new \yii\base\Action('create', $this->controller));
        
        $this->assertEquals(400, \Yii::$app->response->statusCode);
        
        $data = \Yii::$app->response->data;
        $this->assertFalse($data['result']);
        $this->assertArrayHasKey('errors', $data);
        $this->assertArrayHasKey('user_id', $data['errors']);
        $this->assertArrayHasKey('amount', $data['errors']);
        $this->assertArrayHasKey('term', $data['errors']);
    }

    public function testErrorResponseFormatMatchesSpecification(): void
    {
        $this->submitServiceMock
            ->expects($this->never())
            ->method('submit');
        
        $_POST = [
            'user_id' => 'invalid'
        ];
        
        \Yii::$app->request->setBodyParams($_POST);
        
        $this->controller->beforeAction(new \yii\base\Action('create', $this->controller));
        
        $data = \Yii::$app->response->data;
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('result', $data);
        $this->assertArrayHasKey('errors', $data);
        $this->assertFalse($data['result']);
        $this->assertIsArray($data['errors']);
        
        foreach ($data['errors'] as $field => $message) {
            $this->assertIsString($field);
            $this->assertIsString($message);
        }
    }
}
