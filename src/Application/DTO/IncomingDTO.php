<?php

namespace App\Application\DTO;

class IncomingDTO
{
    public function __construct(
        public int $incomingId,
        public string $date,
        public int $plantationId,
        public string $plantationName,
        public float $amount,
        public ?string $note,
        public float $weight,
        public int $incomingTermId,
        public string $incomingTermName,
        public string $buyerName,
        public float $price,
        public bool $paid = false,
        public ?string $datePaid = null,
    ) {
    }
}
