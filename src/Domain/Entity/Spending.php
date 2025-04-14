<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\Money;

class Spending
{
    private ?int $id = null;
    public function __construct(
        private int $plantationId,
        private \DateTimeInterface $date,
        private Money $amount,
        private Note $info

    ) {
    }

}