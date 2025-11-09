<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\LoanRequest;
use App\Domain\Exception\InvalidLoanStatusException;
use App\Domain\Exception\LoanRequestNotFoundException;
use App\Domain\Factory\LoanRequestFactoryInterface;
use App\Domain\Repository\LoanRequestRepositoryInterface;
use App\Domain\Vo\LoanStatus;
use yii\db\Connection;
use yii\db\Exception;

class LoanRequestRepository implements LoanRequestRepositoryInterface
{
    private Connection $db;
    private LoanRequestFactoryInterface $factory;

    public function __construct(Connection $db, LoanRequestFactoryInterface $factory)
    {
        $this->db = $db;
        $this->factory = $factory;
    }

    /**
     * @throws Exception
     */
    public function add(LoanRequest $request): int
    {
        $transaction = $this->db->beginTransaction();

        try {
            $query = 'INSERT INTO loan_requests (user_id, amount, term, status, created_at, updated_at) 
                      VALUES (:userId, :amount, :term, :status, :createdAt, :updatedAt) 
                      RETURNING id';

            $id = $this->db->createCommand($query)
                ->bindValue(':userId', $request->getUserId())
                ->bindValue(':amount', $request->getAmount())
                ->bindValue(':term', $request->getTerm())
                ->bindValue(':status', $request->getStatus()->value)
                ->bindValue(':createdAt', $request->getCreatedAt()->format('Y-m-d H:i:s'))
                ->bindValue(':updatedAt', $request->getUpdatedAt()->format('Y-m-d H:i:s'))
                ->queryScalar();

            $transaction->commit();

            return $id;
        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public function findPendingRequests(): array
    {
        $query = 'SELECT id, user_id, amount, term, status, created_at, updated_at 
                  FROM loan_requests 
                  WHERE status = :status 
                  ORDER BY created_at ASC';

        $rows = $this->db->createCommand($query)
            ->bindValue(':status', LoanStatus::PENDING->value)
            ->queryAll();

        return array_map(fn($row) => $this->factory->createFromData($row), $rows);
    }

    /**
     * @throws Exception
     */
    public function hasApprovedRequest(int $userId): bool
    {
        $query = 'SELECT COUNT(*) FROM loan_requests 
                  WHERE user_id = :userId AND status = :status';

        $count = $this->db->createCommand($query)
            ->bindValue(':userId', $userId)
            ->bindValue(':status', LoanStatus::APPROVED->value)
            ->queryScalar();

        return $count > 0;
    }

    /**
     * @throws Exception
     */
    public function lockAndUpdateStatus(int $requestId, LoanStatus $status): bool
    {
        $transaction = $this->db->beginTransaction();

        try {
            $lockQuery = 'SELECT id, status FROM loan_requests 
                         WHERE id = :id FOR UPDATE NOWAIT';

            $row = $this->db->createCommand($lockQuery)
                ->bindValue(':id', $requestId)
                ->queryOne();

            if ($row === false) {
                throw new LoanRequestNotFoundException($requestId);
            }

            if ($row['status'] !== LoanStatus::PENDING->value) {
                throw new InvalidLoanStatusException($requestId, $row['status']);
            }

            $updateQuery = 'UPDATE loan_requests 
                           SET status = :status, updated_at = CURRENT_TIMESTAMP 
                           WHERE id = :id';

            $affectedRows = $this->db->createCommand($updateQuery)
                ->bindValue(':status', $status->value)
                ->bindValue(':id', $requestId)
                ->execute();

            $transaction->commit();

            return $affectedRows > 0;
        } catch (Exception $e) {
            $transaction->rollBack();
            //todo: logs

            throw $e;
        }
    }
}
