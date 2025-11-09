<?php

namespace App\Application\UseCase;

use App\Domain\Factory\LoanRequestFactoryInterface;
use App\Domain\Repository\LoanRequestRepositoryInterface;
use App\Domain\Service\LoanEligibilityService;

class SubmitLoanRequestService
{
    private LoanRequestRepositoryInterface $repository;
    private LoanEligibilityService $eligibilityService;
    private LoanRequestFactoryInterface $factory;

    public function __construct(
        LoanRequestRepositoryInterface $repository,
        LoanEligibilityService $eligibilityService,
        LoanRequestFactoryInterface $factory
    ) {
        $this->repository = $repository;
        $this->eligibilityService = $eligibilityService;
        $this->factory = $factory;
    }

    public function submit(int $userId, int $amount, int $term): ?int
    {
        if (!$this->eligibilityService->isEligibleForNewLoan($userId)) {
            return null;
        }

        $loanRequest = $this->factory->create($userId, $amount, $term);

        return $this->repository->add($loanRequest);
    }
}