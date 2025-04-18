<?php

namespace App\Application\UseCase\EditIncoming;

class EditIncomingRequest
{
    public function __construct(
        public int $incomingId,
        public int $plantationId,
        public string $date,
        public ?string $note,
        public float $weight,
        public int $incomingTermId,
        public string $buyerName,
        public float $price,
        public ?bool $paid = false,
        public ?string $datePaid = null
    ) {
    }
}
