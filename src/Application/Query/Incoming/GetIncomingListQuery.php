<?php

namespace App\Application\Query\Incoming;

use App\Domain\ValueObject\Date;

class GetIncomingListQuery
{
    public function __construct(
        private readonly ?int $plantationId = null,
        private readonly ?Date $dateFrom = null,
        private readonly ?Date $dateTo = null
    ) {
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
