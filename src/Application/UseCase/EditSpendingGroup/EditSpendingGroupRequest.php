<?php

namespace App\Application\UseCase\EditSpendingGroup;

class EditSpendingGroupRequest
{
    public function __construct(
        public int $spendingGroupId,
        public int $spendingTypeId,
        public string $date,
        public float $amount,
        public ?string $note
    ) {
    }
}
