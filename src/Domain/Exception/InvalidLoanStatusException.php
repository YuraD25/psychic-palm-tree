<?php

namespace App\Domain\Exception;

use RuntimeException;

class InvalidLoanStatusException extends RuntimeException
{
    public function __construct(int $requestId, string $currentStatus)
    {
        parent::__construct("Loan request $requestId has invalid status: $currentStatus for this operation");
    }
}
