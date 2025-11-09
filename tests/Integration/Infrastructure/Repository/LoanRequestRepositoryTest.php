<?php

namespace Tests\Integration\Infrastructure\Repository;

use App\Infrastructure\Repository\LoanRequestRepository;
use App\Domain\Entity\LoanRequest;
use App\Domain\Vo\LoanStatus;
use PHPUnit\Framework\TestCase;

class LoanRequestRepositoryTest extends TestCase
{
    public function testRepositoryImplementsCorrectInterface(): void
    {
        $reflection = new \ReflectionClass(LoanRequestRepository::class);
        $interfaces = $reflection->getInterfaceNames();
        
        $this->assertContains(
            'App\Domain\Repository\LoanRequestRepositoryInterface',
            $interfaces,
            'LoanRequestRepository must implement LoanRequestRepositoryInterface'
        );
    }

    public function testRepositoryHasRequiredMethods(): void
    {
        $reflection = new \ReflectionClass(LoanRequestRepository::class);
        
        $requiredMethods = [
            'add',
            'findPendingRequests',
            'hasApprovedRequest',
            'lockAndUpdateStatus'
        ];
        
        foreach ($requiredMethods as $method) {
            $this->assertTrue(
                $reflection->hasMethod($method),
                "Repository must have method: {$method}"
            );
        }
    }

    public function testAddMethodSignature(): void
    {
        $reflection = new \ReflectionClass(LoanRequestRepository::class);
        $method = $reflection->getMethod('add');
        
        $this->assertEquals(1, $method->getNumberOfParameters());
        
        $parameter = $method->getParameters()[0];
        $this->assertEquals('request', $parameter->getName());
        
        if ($method->hasReturnType()) {
            $returnType = $method->getReturnType();
            $this->assertEquals('int', $returnType->getName());
        }
    }

    public function testLockAndUpdateStatusMethodSignature(): void
    {
        $reflection = new \ReflectionClass(LoanRequestRepository::class);
        $method = $reflection->getMethod('lockAndUpdateStatus');
        
        $this->assertEquals(2, $method->getNumberOfParameters());
        
        $parameters = $method->getParameters();
        $this->assertEquals('requestId', $parameters[0]->getName());
        $this->assertEquals('status', $parameters[1]->getName());
        
        if ($method->hasReturnType()) {
            $returnType = $method->getReturnType();
            $this->assertEquals('bool', $returnType->getName());
        }
    }
}
