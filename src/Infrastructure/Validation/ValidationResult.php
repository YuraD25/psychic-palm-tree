<?php

declare(strict_types=1);

namespace App\Infrastructure\Validation;

final readonly class ValidationResult
{
    public function __construct(
        public bool $isValid,
        public array $errors = [],
        public ?object $dto = null
    ) {}
}
