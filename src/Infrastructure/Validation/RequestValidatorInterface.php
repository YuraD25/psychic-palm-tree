<?php

declare(strict_types=1);

namespace App\Infrastructure\Validation;

interface RequestValidatorInterface
{
    public function validate(array $data): ValidationResult;
}
