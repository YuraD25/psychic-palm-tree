<?php

namespace App\Domain\Factory;

use App\Domain\Entity\LoanRequest;

interface LoanRequestFactoryInterface
{
    public function create(int $userId, int $amount, int $term): LoanRequest;
    
    public function createFromData(array $data): LoanRequest;
}
