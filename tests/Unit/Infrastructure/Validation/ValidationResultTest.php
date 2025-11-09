<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Validation;

use App\Infrastructure\Validation\ValidationResult;
use App\Infrastructure\Validation\DTO\SubmitLoanRequestDTO;
use PHPUnit\Framework\TestCase;

class ValidationResultTest extends TestCase
{
    public function testCreationWithValidState(): void
    {
        $dto = new SubmitLoanRequestDTO(
            userId: 1,
            amount: 5000,
            term: 12
        );

        $result = new ValidationResult(
            isValid: true,
            errors: [],
            dto: $dto
        );

        $this->assertTrue($result->isValid);
        $this->assertEmpty($result->errors);
        $this->assertSame($dto, $result->dto);
    }

    public function testCreationWithErrors(): void
    {
        $errors = [
            'user_id' => 'Field is required',
            'amount' => 'Must be a positive integer'
        ];

        $result = new ValidationResult(
            isValid: false,
            errors: $errors
        );

        $this->assertFalse($result->isValid);
        $this->assertEquals($errors, $result->errors);
        $this->assertNull($result->dto);
    }

    public function testReadonlyPropertyBehavior(): void
    {
        $result = new ValidationResult(
            isValid: true,
            errors: [],
            dto: null
        );

        $this->assertTrue($result->isValid);
        $this->assertEmpty($result->errors);
        $this->assertNull($result->dto);
    }
}
