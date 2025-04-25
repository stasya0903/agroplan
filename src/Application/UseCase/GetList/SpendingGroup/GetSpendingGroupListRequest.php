<?php

namespace App\Application\UseCase\GetList\SpendingGroup;

class GetSpendingGroupListRequest
{
    public function __construct(
        public ?int $plantationId = null,
        public ?int $spendingTypeId = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null
    ) {
    }
}
