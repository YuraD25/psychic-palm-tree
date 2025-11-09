<?php

declare(strict_types=1);

namespace App\Infrastructure\Validation;

use Yii;
use yii\base\ActionEvent;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\rest\Controller;
use yii\web\Response;

class ValidationBehavior extends Behavior
{
    public array $validators = [];

    public function events(): array
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction'
        ];
    }

    /**
     * @throws InvalidConfigException
     */
    public function beforeAction(ActionEvent $event): bool
    {
        $action = $event->action;
        $actionId = $action->id;

        if (!isset($this->validators[$actionId])) {
            return true;
        }

        $validatorClass = $this->validators[$actionId];

        if (!class_exists($validatorClass)) {
            return true;
        }

        $validator = Yii::createObject($validatorClass);

        if (!$validator instanceof RequestValidatorInterface) {
            return true;
        }

        $request = Yii::$app->request;
        $data = $request->getBodyParams();

        if (empty($data)) {
            $data = json_decode($request->getRawBody(), true) ?? [];
        }

        $result = $validator->validate($data);

        if (!$result->isValid) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            Yii::$app->response->statusCode = 400;
            Yii::$app->response->data = [
                'result' => false,
                'errors' => $result->errors
            ];

            $event->isValid = false;

            return false;
        }

        $this->owner->validatedData = $result->dto;

        return true;
    }
}

