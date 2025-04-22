<?php

namespace App\Application\UseCase\CreateSpending;

class CreateSpendingRequest
{
    public function __construct(
        public int $plantationId,
        public int $spendingTypeId,
        public string $date,
        public float $amount,
        public ?string $note
    ) {
    }
}
