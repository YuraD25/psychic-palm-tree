<?php

namespace Tests\Integration\Application\UseCase;

use App\Application\UseCase\SubmitLoanRequestService;
use App\Domain\Entity\LoanRequest;
use App\Domain\Repository\LoanRequestRepositoryInterface;
use App\Domain\Service\LoanEligibilityService;
use App\Infrastructure\Factory\LoanRequestFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SubmitLoanRequestServiceTest extends TestCase
{
    private SubmitLoanRequestService $service;
    private MockObject $repositoryMock;
    private MockObject $eligibilityServiceMock;
    private LoanRequestFactory $factory;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(LoanRequestRepositoryInterface::class);
        $this->eligibilityServiceMock = $this->createMock(LoanEligibilityService::class);
        $this->factory = new LoanRequestFactory();
        
        $this->service = new SubmitLoanRequestService(
            $this->repositoryMock,
            $this->eligibilityServiceMock,
            $this->factory
        );
    }

    public function testSubmitSuccessfullyCreatesLoanRequest(): void
    {
        $userId = 1;
        $amount = 5000;
        $term = 12;
        $expectedId = 42;

        // Mock eligibility check to return true
        $this->eligibilityServiceMock
            ->expects($this->once())
            ->method('isEligibleForNewLoan')
            ->with($userId)
            ->willReturn(true);

        // Mock repository add to return ID
        $this->repositoryMock
            ->expects($this->once())
            ->method('add')
            ->willReturn($expectedId);

        $result = $this->service->submit($userId, $amount, $term);

        $this->assertEquals($expectedId, $result);
    }

    public function testSubmitReturnsNullWhenUserNotEligible(): void
    {
        $userId = 1;
        $amount = 5000;
        $term = 12;

        // Mock eligibility check to return false
        $this->eligibilityServiceMock
            ->expects($this->once())
            ->method('isEligibleForNewLoan')
            ->with($userId)
            ->willReturn(false);

        // Repository add should not be called
        $this->repositoryMock
            ->expects($this->never())
            ->method('add');

        $result = $this->service->submit($userId, $amount, $term);

        $this->assertNull($result);
    }

    public function testSubmitCreatesLoanRequestWithCorrectParameters(): void
    {
        $userId = 1;
        $amount = 5000;
        $term = 12;
        $expectedId = 42;

        $this->eligibilityServiceMock
            ->method('isEligibleForNewLoan')
            ->willReturn(true);

        // Verify the loan request is created with correct parameters
        $this->repositoryMock
            ->expects($this->once())
            ->method('add')
            ->willReturnCallback(function (LoanRequest $request) use ($userId, $amount, $term, $expectedId) {
                $this->assertEquals($userId, $request->getUserId());
                $this->assertEquals($amount, $request->getAmount());
                $this->assertEquals($term, $request->getTerm());
                $this->assertEquals(\App\Domain\Vo\LoanStatus::PENDING, $request->getStatus());
                
                return $expectedId;
            });

        $result = $this->service->submit($userId, $amount, $term);

        $this->assertEquals($expectedId, $result);
    }
}