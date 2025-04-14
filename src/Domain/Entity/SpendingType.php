<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\Money;

class SpendingType
{
    private ?int $id = null;
    public function __construct(
        private Name $spendingTypeName,
    ) {
    }

}