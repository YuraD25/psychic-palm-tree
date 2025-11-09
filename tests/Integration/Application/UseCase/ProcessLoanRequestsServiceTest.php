<?php

namespace Tests\Integration\Application\UseCase;

use App\Application\UseCase\ProcessLoanRequestsService;
use App\Domain\Repository\LoanRequestRepositoryInterface;
use App\Domain\Repository\ProcessingDelayInterface;
use App\Domain\Service\LoanDecisionService;
use App\Domain\Entity\LoanRequest;
use App\Domain\Vo\LoanStatus;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use DateTime;

class ProcessLoanRequestsServiceTest extends TestCase
{
    private ProcessLoanRequestsService $service;
    private MockObject $repositoryMock;
    private MockObject $decisionServiceMock;
    private MockObject $delayServiceMock;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(LoanRequestRepositoryInterface::class);
        $this->decisionServiceMock = $this->createMock(LoanDecisionService::class);
        $this->delayServiceMock = $this->createMock(ProcessingDelayInterface::class);
        
        $this->service = new ProcessLoanRequestsService(
            $this->repositoryMock,
            $this->decisionServiceMock,
            $this->delayServiceMock
        );
    }

    public function testProcessAppliesDelayBeforeProcessing(): void
    {
        $delay = 5;
        
        $this->delayServiceMock
            ->expects($this->once())
            ->method('delay')
            ->with($delay);

        $this->repositoryMock
            ->method('findPendingRequests')
            ->willReturn([]);

        $result = $this->service->process($delay);

        $this->assertTrue($result);
    }

    public function testProcessHandlesEmptyPendingRequests(): void
    {
        $delay = 1;
        
        $this->delayServiceMock
            ->method('delay')
            ->with($delay);

        $this->repositoryMock
            ->expects($this->once())
            ->method('findPendingRequests')
            ->willReturn([]);

        // Decision service should not be called for empty list
        $this->decisionServiceMock
            ->expects($this->never())
            ->method('makeDecision');

        $result = $this->service->process($delay);

        $this->assertTrue($result);
    }

    public function testProcessMakesDecisionForEachPendingRequest(): void
    {
        $delay = 1;
        
        // Create mock loan requests
        $request1 = $this->createMockLoanRequest(1);
        $request2 = $this->createMockLoanRequest(2);
        $pendingRequests = [$request1, $request2];

        $this->delayServiceMock
            ->method('delay')
            ->with($delay);

        $this->repositoryMock
            ->method('findPendingRequests')
            ->willReturn($pendingRequests);

        // Decision service should be called twice
        $this->decisionServiceMock
            ->expects($this->exactly(2))
            ->method('makeDecision')
            ->willReturnOnConsecutiveCalls(
                LoanStatus::APPROVED,
                LoanStatus::DECLINED
            );

        // Repository should update status for each request
        $this->repositoryMock
            ->expects($this->exactly(2))
            ->method('lockAndUpdateStatus')
            ->withConsecutive(
                [1, LoanStatus::APPROVED],
                [2, LoanStatus::DECLINED]
            )
            ->willReturn(true);

        $result = $this->service->process($delay);

        $this->assertTrue($result);
    }

    public function testProcessContinuesEvenIfSomeUpdatesFailure(): void
    {
        $delay = 1;
        
        $request1 = $this->createMockLoanRequest(1);
        $request2 = $this->createMockLoanRequest(2);
        $pendingRequests = [$request1, $request2];

        $this->delayServiceMock
            ->method('delay')
            ->with($delay);

        $this->repositoryMock
            ->method('findPendingRequests')
            ->willReturn($pendingRequests);

        $this->decisionServiceMock
            ->method('makeDecision')
            ->willReturnOnConsecutiveCalls(
                LoanStatus::APPROVED,
                LoanStatus::DECLINED
            );

        // First update fails, second succeeds
        $this->repositoryMock
            ->method('lockAndUpdateStatus')
            ->willReturnOnConsecutiveCalls(false, true);

        $result = $this->service->process($delay);

        $this->assertTrue($result);
    }

    private function createMockLoanRequest(int $id): LoanRequest
    {
        $request = new LoanRequest();
        $request->setUserId(1);
        $request->setAmount(5000);
        $request->setTerm(12);
        $request->setStatus(LoanStatus::PENDING);
        $request->setCreatedAt(new \DateTime());
        $request->setUpdatedAt(new \DateTime());
        
        $reflection = new \ReflectionClass($request);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($request, $id);
        
        return $request;
    }
}