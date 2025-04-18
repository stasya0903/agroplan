<?php

namespace App\Application\DTO;

class IncomingDTO
{
    public function __construct(
        public int $id,
        public string $date,
        public string $plantationId,
        public string $plantationName,
        public float $amount,
        public ?string $note,
        public float $weight,
        public int $incomingTypeId,
        public string $incomingTypeName,
        public string $buyerName,
        public float $price,
        public bool $paid = false,
        public ?string $datePaid = null,
    ) {
    }
}
