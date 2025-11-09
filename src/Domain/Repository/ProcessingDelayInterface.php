<?php

namespace App\Domain\Repository;

interface ProcessingDelayInterface
{
    public function delay(int $seconds): void;
}