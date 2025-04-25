<?php

namespace App\Application\DTO;

class SpendingDTO
{
    public function __construct(
        public int $id,
        public int $plantationId,
        public string $plantationName,
        public float $amount,
        public ?string $spendingTypeName = null,
        public ?string $date = null,
        public ?string $info = null,
    ) {
    }
}
