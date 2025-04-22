<?php

namespace App\Application\UseCase\CreateIncoming;

class CreateIncomingRequest
{
    public function __construct(
        public int $plantationId,
        public string $date,
        public ?string $note,
        public float $weight,
        public int $incomingTermId,
        public string $buyerName,
        public float $price
    ) {
    }
}
