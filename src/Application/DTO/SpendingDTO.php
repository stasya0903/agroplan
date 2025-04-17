<?php

namespace App\Application\DTO;

class SpendingDTO
{
    public function __construct(
        public int $id,
        public string $date,
        public string $plantationId,
        public string $plantationName,
        public int $spendingTypeId,
        public string $SpendingName,
        public float $amount,
        public ?string $note
    ) {
    }
}
