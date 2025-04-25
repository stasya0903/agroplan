<?php

namespace App\Application\Query\SpendingGroup;

use App\Domain\ValueObject\Date;

class GetSpendingGroupListQuery
{
    public function __construct(
        private readonly ?int $spendingGroupTypeId = null,
        private readonly ?int $plantationId = null,
        private readonly ?Date $dateFrom = null,
        private readonly ?Date $dateTo = null
    ) {
    }

    public function getSpendingGroupTypeId(): ?int
    {
        return $this->spendingGroupTypeId;
    }

    public function getDateFrom(): ?Date
    {
        return $this->dateFrom;
    }

    public function getDateTo(): ?Date
    {
        return $this->dateTo;
    }

    public function getPlantationId(): ?string
    {
        return $this->plantationId;
    }
}
