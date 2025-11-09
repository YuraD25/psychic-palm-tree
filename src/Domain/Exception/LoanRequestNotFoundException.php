<?php

namespace App\Domain\Exception;

use RuntimeException;

class LoanRequestNotFoundException extends RuntimeException
{
    public function __construct(int $requestId)
    {
        parent::__construct("Loan request with ID $requestId not found");
    }
}
