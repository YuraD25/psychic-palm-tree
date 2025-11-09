<?php

namespace App\Infrastructure\Service;

use App\Domain\Repository\ProcessingDelayInterface;

class ProcessingDelayService implements ProcessingDelayInterface
{
    public function delay(int $seconds): void
    {
        if ($seconds > 0) {
            sleep($seconds);
        }
    }
}