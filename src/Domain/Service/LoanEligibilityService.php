<?php

namespace App\Domain\Service;

use App\Domain\Repository\LoanRequestRepositoryInterface;

class LoanEligibilityService
{
    private LoanRequestRepositoryInterface $repository;

    public function __construct(LoanRequestRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function isEligibleForNewLoan(int $userId): bool
    {
        return !$this->repository->hasApprovedRequest($userId);
    }
}