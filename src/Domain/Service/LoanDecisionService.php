<?php

namespace App\Domain\Service;

use App\Domain\Vo\LoanStatus;

class LoanDecisionService
{
    private const APPROVAL_PROBABILITY = 0.1; // 10%

    public function makeDecision(): LoanStatus
    {
        $randomValue = mt_rand(1, 100) / 100.0;
        
        return $randomValue <= self::APPROVAL_PROBABILITY 
            ? LoanStatus::APPROVED 
            : LoanStatus::DECLINED;
    }

    public function getApprovalProbability(): float
    {
        return self::APPROVAL_PROBABILITY;
    }
}