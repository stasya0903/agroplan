<?php

namespace App\Application\UseCase\EditSpending;

class EditSpendingRequest
{
    public function __construct(
        public int $spendingId,
        public int $plantationId,
        public float $amount
    ) {
    }
}
