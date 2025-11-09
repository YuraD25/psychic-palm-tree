<?php

namespace App\Domain\Vo;

enum LoanStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case DECLINED = 'declined';
}