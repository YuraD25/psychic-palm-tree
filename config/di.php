<?php

use App\Adapter\Http\LoanRequestController;
use App\Adapter\Http\ProcessorController;
use App\Application\UseCase\ProcessLoanRequestsService;
use App\Application\UseCase\SubmitLoanRequestService;
use App\Domain\Factory\LoanRequestFactoryInterface;
use App\Domain\Repository\LoanRequestRepositoryInterface;
use App\Domain\Repository\ProcessingDelayInterface;
use App\Domain\Service\LoanDecisionService;
use App\Domain\Service\LoanEligibilityService;
use App\Infrastructure\Factory\LoanRequestFactory;
use App\Infrastructure\Repository\LoanRequestRepository;
use App\Infrastructure\Service\ProcessingDelayService;
use yii\di\Instance;

return [
    'definitions' => [
        // Repository interfaces to concrete implementations
        LoanRequestRepositoryInterface::class => function($container) {
            return new LoanRequestRepository(
                \Yii::$app->db,
                $container->get(LoanRequestFactoryInterface::class)
            );
        },
        ProcessingDelayInterface::class => ProcessingDelayService::class,
        
        // Domain services and factories
        LoanRequestFactoryInterface::class => LoanRequestFactory::class,
        LoanDecisionService::class => LoanDecisionService::class,
        LoanEligibilityService::class => [
            'class' => LoanEligibilityService::class,
            '__construct()' => [
                Instance::of(LoanRequestRepositoryInterface::class),
            ],
        ],
        
        // Application use case services
        SubmitLoanRequestService::class => [
            'class' => SubmitLoanRequestService::class,
            '__construct()' => [
                Instance::of(LoanRequestRepositoryInterface::class),
                Instance::of(LoanEligibilityService::class),
                Instance::of(LoanRequestFactoryInterface::class),
            ],
        ],
        ProcessLoanRequestsService::class => [
            'class' => ProcessLoanRequestsService::class,
            '__construct()' => [
                Instance::of(LoanRequestRepositoryInterface::class),
                Instance::of(LoanDecisionService::class),
                Instance::of(ProcessingDelayInterface::class),
            ],
        ],
        
        // Controllers
        LoanRequestController::class => [
            'class' => LoanRequestController::class,
            '__construct()' => [
                Instance::of(SubmitLoanRequestService::class),
            ],
        ],
        ProcessorController::class => [
            'class' => ProcessorController::class,
            '__construct()' => [
                Instance::of(ProcessLoanRequestsService::class),
            ],
        ],
    ],
];