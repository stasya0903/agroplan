<?php

namespace App\Application\DTO;

class SpendingGroupDTO
{
    /**
     * @param SpendingDTO[] $spending
     */
    public function __construct(
        public int $id,
        public string $date,
        public int $spendingTypeId,
        public string $spendingTypeName,
        public float $amount,
        public ?string $note = null,
        public ?array $spending = []
    ) {
    }
}
