<?php

namespace App\Application\UseCase;

use App\Domain\Repository\LoanRequestRepositoryInterface;
use App\Domain\Repository\ProcessingDelayInterface;
use App\Domain\Service\LoanDecisionService;

class ProcessLoanRequestsService
{
    private LoanRequestRepositoryInterface $repository;
    private LoanDecisionService $decisionService;
    private ProcessingDelayInterface $delayService;

    public function __construct(
        LoanRequestRepositoryInterface $repository,
        LoanDecisionService $decisionService,
        ProcessingDelayInterface $delayService
    ) {
        $this->repository = $repository;
        $this->decisionService = $decisionService;
        $this->delayService = $delayService;
    }

    public function process(int $delay): bool
    {
        $this->delayService->delay($delay);

        $pendingRequests = $this->repository->findPendingRequests();

        foreach ($pendingRequests as $request) {
            $decision = $this->decisionService->makeDecision();

            $this->repository->lockAndUpdateStatus($request->getId(), $decision);
        }

        return true;
    }
}
