<?php

namespace Tests\Unit\Domain\Service;

use App\Domain\Service\LoanDecisionService;
use App\Domain\Vo\LoanStatus;
use PHPUnit\Framework\TestCase;

class LoanDecisionServiceTest extends TestCase
{
    private LoanDecisionService $service;

    protected function setUp(): void
    {
        $this->service = new LoanDecisionService();
    }

    public function testGetApprovalProbabilityReturns10Percent(): void
    {
        $probability = $this->service->getApprovalProbability();
        
        $this->assertEquals(0.1, $probability);
    }

    public function testMakeDecisionReturnsValidLoanStatus(): void
    {
        $decision = $this->service->makeDecision();
        
        $this->assertInstanceOf(LoanStatus::class, $decision);
        $this->assertContains($decision, [LoanStatus::APPROVED, LoanStatus::DECLINED]);
    }

    public function testMakeDecisionProbabilityDistribution(): void
    {
        $approvalCount = 0;
        $totalDecisions = 1000;
        
        // Run multiple decisions to test probability distribution
        for ($i = 0; $i < $totalDecisions; $i++) {
            $decision = $this->service->makeDecision();
            if ($decision === LoanStatus::APPROVED) {
                $approvalCount++;
            }
        }
        
        $actualProbability = $approvalCount / $totalDecisions;
        
        // Allow for some variance in random distribution (±5%)
        $this->assertGreaterThanOrEqual(0.05, $actualProbability);
        $this->assertLessThanOrEqual(0.15, $actualProbability);
    }
}