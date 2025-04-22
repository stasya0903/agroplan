<?php

namespace App\Application\Query\Spending;

use App\Domain\ValueObject\Date;

class GetSpendingListQuery
{
    public function __construct(
        private readonly ?int $spendingTypeId = null,
        private readonly ?int $plantationId = null,
        private readonly ?Date $dateFrom = null,
        private readonly ?Date $dateTo = null
    ) {
    }

    public function getSpendingTypeId(): ?int
    {
        return $this->spendingTypeId;
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
