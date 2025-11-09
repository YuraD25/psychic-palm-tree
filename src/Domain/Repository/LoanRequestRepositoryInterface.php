<?php

namespace App\Domain\Repository;

use App\Domain\Entity\LoanRequest;
use App\Domain\Vo\LoanStatus;

interface LoanRequestRepositoryInterface
{
    public function add(LoanRequest $request): int;

    public function findPendingRequests(): array;
    
    public function hasApprovedRequest(int $userId): bool;
    
    public function lockAndUpdateStatus(int $requestId, LoanStatus $status): bool;
}