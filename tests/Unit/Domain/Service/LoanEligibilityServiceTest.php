<?php

namespace Tests\Unit\Domain\Service;

use App\Domain\Service\LoanEligibilityService;
use App\Domain\Repository\LoanRequestRepositoryInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class LoanEligibilityServiceTest extends TestCase
{
    private LoanEligibilityService $service;
    private MockObject $repositoryMock;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(LoanRequestRepositoryInterface::class);
        $this->service = new LoanEligibilityService($this->repositoryMock);
    }

    public function testIsEligibleForNewLoanReturnsTrueWhenNoApprovedRequests(): void
    {
        $userId = 1;
        
        $this->repositoryMock
            ->expects($this->once())
            ->method('hasApprovedRequest')
            ->with($userId)
            ->willReturn(false);
        
        $result = $this->service->isEligibleForNewLoan($userId);
        
        $this->assertTrue($result);
    }

    public function testIsEligibleForNewLoanReturnsFalseWhenHasApprovedRequests(): void
    {
        $userId = 1;
        
        $this->repositoryMock
            ->expects($this->once())
            ->method('hasApprovedRequest')
            ->with($userId)
            ->willReturn(true);
        
        $result = $this->service->isEligibleForNewLoan($userId);
        
        $this->assertFalse($result);
    }
}