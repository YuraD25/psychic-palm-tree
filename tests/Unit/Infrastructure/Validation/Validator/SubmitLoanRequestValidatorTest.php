<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Validation\Validator;

use App\Infrastructure\Validation\Validator\SubmitLoanRequestValidator;
use App\Infrastructure\Validation\DTO\SubmitLoanRequestDTO;
use PHPUnit\Framework\TestCase;

class SubmitLoanRequestValidatorTest extends TestCase
{
    private SubmitLoanRequestValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new SubmitLoanRequestValidator();
    }

    public function testValidateWithValidDataReturnsSuccessAndDto(): void
    {
        $data = [
            'user_id' => 1,
            'amount' => 5000,
            'term' => 12
        ];

        $result = $this->validator->validate($data);

        $this->assertTrue($result->isValid);
        $this->assertEmpty($result->errors);
        $this->assertInstanceOf(SubmitLoanRequestDTO::class, $result->dto);
        $this->assertEquals(1, $result->dto->userId);
        $this->assertEquals(5000, $result->dto->amount);
        $this->assertEquals(12, $result->dto->term);
    }

    public function testValidateWithMissingUserIdReturnsError(): void
    {
        $data = [
            'amount' => 5000,
            'term' => 12
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid);
        $this->assertArrayHasKey('user_id', $result->errors);
        $this->assertEquals('Field is required', $result->errors['user_id']);
        $this->assertNull($result->dto);
    }

    public function testValidateWithMissingAmountReturnsError(): void
    {
        $data = [
            'user_id' => 1,
            'term' => 12
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid);
        $this->assertArrayHasKey('amount', $result->errors);
        $this->assertEquals('Field is required', $result->errors['amount']);
        $this->assertNull($result->dto);
    }

    public function testValidateWithMissingTermReturnsError(): void
    {
        $data = [
            'user_id' => 1,
            'amount' => 5000
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid);
        $this->assertArrayHasKey('term', $result->errors);
        $this->assertEquals('Field is required', $result->errors['term']);
        $this->assertNull($result->dto);
    }

    public function testValidateWithNonNumericUserIdReturnsError(): void
    {
        $data = [
            'user_id' => 'abc',
            'amount' => 5000,
            'term' => 12
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid);
        $this->assertArrayHasKey('user_id', $result->errors);
        $this->assertEquals('Must be a positive integer', $result->errors['user_id']);
        $this->assertNull($result->dto);
    }

    public function testValidateWithNonNumericAmountReturnsError(): void
    {
        $data = [
            'user_id' => 1,
            'amount' => 'invalid',
            'term' => 12
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid);
        $this->assertArrayHasKey('amount', $result->errors);
        $this->assertEquals('Must be a positive integer', $result->errors['amount']);
        $this->assertNull($result->dto);
    }

    public function testValidateWithNonNumericTermReturnsError(): void
    {
        $data = [
            'user_id' => 1,
            'amount' => 5000,
            'term' => 'twelve'
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid);
        $this->assertArrayHasKey('term', $result->errors);
        $this->assertEquals('Must be a positive integer', $result->errors['term']);
        $this->assertNull($result->dto);
    }

    public function testValidateWithNegativeUserIdReturnsError(): void
    {
        $data = [
            'user_id' => -1,
            'amount' => 5000,
            'term' => 12
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid);
        $this->assertArrayHasKey('user_id', $result->errors);
        $this->assertEquals('Must be a positive integer', $result->errors['user_id']);
        $this->assertNull($result->dto);
    }

    public function testValidateWithNegativeAmountReturnsError(): void
    {
        $data = [
            'user_id' => 1,
            'amount' => -5000,
            'term' => 12
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid);
        $this->assertArrayHasKey('amount', $result->errors);
        $this->assertEquals('Must be a positive integer', $result->errors['amount']);
        $this->assertNull($result->dto);
    }

    public function testValidateWithNegativeTermReturnsError(): void
    {
        $data = [
            'user_id' => 1,
            'amount' => 5000,
            'term' => -12
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid);
        $this->assertArrayHasKey('term', $result->errors);
        $this->assertEquals('Must be a positive integer', $result->errors['term']);
        $this->assertNull($result->dto);
    }

    public function testValidateWithZeroUserIdReturnsError(): void
    {
        $data = [
            'user_id' => 0,
            'amount' => 5000,
            'term' => 12
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid);
        $this->assertArrayHasKey('user_id', $result->errors);
        $this->assertEquals('Must be a positive integer', $result->errors['user_id']);
        $this->assertNull($result->dto);
    }

    public function testValidateWithZeroAmountReturnsError(): void
    {
        $data = [
            'user_id' => 1,
            'amount' => 0,
            'term' => 12
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid);
        $this->assertArrayHasKey('amount', $result->errors);
        $this->assertEquals('Must be a positive integer', $result->errors['amount']);
        $this->assertNull($result->dto);
    }

    public function testValidateWithZeroTermReturnsError(): void
    {
        $data = [
            'user_id' => 1,
            'amount' => 5000,
            'term' => 0
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid);
        $this->assertArrayHasKey('term', $result->errors);
        $this->assertEquals('Must be a positive integer', $result->errors['term']);
        $this->assertNull($result->dto);
    }

    public function testValidateWithMultipleErrorsCollectsAllErrors(): void
    {
        $data = [
            'user_id' => -1,
            'amount' => 'invalid',
            'term' => 0
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid);
        $this->assertCount(3, $result->errors);
        $this->assertArrayHasKey('user_id', $result->errors);
        $this->assertArrayHasKey('amount', $result->errors);
        $this->assertArrayHasKey('term', $result->errors);
        $this->assertNull($result->dto);
    }
}
