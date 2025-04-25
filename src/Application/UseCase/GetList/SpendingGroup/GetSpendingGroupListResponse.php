<?php

namespace App\Application\UseCase\GetList\SpendingGroup;

use App\Application\DTO\SpendingGroupDTO;

class GetSpendingGroupListResponse
{
    /**
     * @param SpendingGroupDTO[] $spendingGroups
     * @param float $total
     */
    public function __construct(
        public array $spendingGroups,
        public float $total
    ) {
    }
}
