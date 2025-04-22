<?php

namespace App\Application\UseCase\GetList\Spending;

class GetSpendingListRequest
{
    public function __construct(
        public ?int $plantationId = null,
        public ?int $spendingTypeId = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null
    ) {
    }
}
