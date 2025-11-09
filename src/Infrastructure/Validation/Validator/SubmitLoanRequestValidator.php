<?php

declare(strict_types=1);

namespace App\Infrastructure\Validation\Validator;

use App\Infrastructure\Validation\RequestValidatorInterface;
use App\Infrastructure\Validation\ValidationResult;
use App\Infrastructure\Validation\DTO\SubmitLoanRequestDTO;

final class SubmitLoanRequestValidator implements RequestValidatorInterface
{
    public function validate(array $data): ValidationResult
    {
        $errors = [];

        if (!isset($data['user_id'])) {
            $errors['user_id'] = 'Field is required';
        } elseif (!is_numeric($data['user_id']) || (int)$data['user_id'] <= 0) {
            $errors['user_id'] = 'Must be a positive integer';
        }

        if (!isset($data['amount'])) {
            $errors['amount'] = 'Field is required';
        } elseif (!is_numeric($data['amount']) || (int)$data['amount'] <= 0) {
            $errors['amount'] = 'Must be a positive integer';
        }

        if (!isset($data['term'])) {
            $errors['term'] = 'Field is required';
        } elseif (!is_numeric($data['term']) || (int)$data['term'] <= 0) {
            $errors['term'] = 'Must be a positive integer';
        }

        if (!empty($errors)) {
            return new ValidationResult(false, $errors);
        }

        $dto = new SubmitLoanRequestDTO(
            userId: (int)$data['user_id'],
            amount: (int)$data['amount'],
            term: (int)$data['term']
        );

        return new ValidationResult(true, [], $dto);
    }
}
