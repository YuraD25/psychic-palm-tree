<?php

declare(strict_types=1);

namespace App\Infrastructure\Factory;

use App\Domain\Entity\LoanRequest;
use App\Domain\Factory\LoanRequestFactoryInterface;
use App\Domain\Vo\LoanStatus;
use DateTime;

class LoanRequestFactory implements LoanRequestFactoryInterface
{
    public function create(int $userId, int $amount, int $term): LoanRequest
    {
        return (new LoanRequest())
            ->setUserId($userId)
            ->setAmount($amount)
            ->setTerm($term)
            ->setStatus(LoanStatus::PENDING)
            ->setCreatedAt(new DateTime())
            ->setUpdatedAt(new DateTime());
    }

    /**
     * @throws \Exception
     */
    public function createFromData(array $data): LoanRequest
    {
        return (new LoanRequest())
            ->setId((int)$data['id'])
            ->setUserId((int)$data['user_id'])
            ->setAmount((int)$data['amount'])
            ->setTerm((int)$data['term'])
            ->setStatus(LoanStatus::from($data['status']))
            ->setCreatedAt(new DateTime($data['created_at']))
            ->setUpdatedAt(new DateTime($data['updated_at']));
    }
}
