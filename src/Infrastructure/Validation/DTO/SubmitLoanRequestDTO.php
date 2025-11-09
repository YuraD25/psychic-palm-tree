<?php

declare(strict_types=1);

namespace App\Infrastructure\Validation\DTO;

final readonly class SubmitLoanRequestDTO
{
    public function __construct(
        public int $userId,
        public int $amount,
        public int $term
    ) {}
}
