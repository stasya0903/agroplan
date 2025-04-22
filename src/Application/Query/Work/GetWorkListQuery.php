<?php

namespace App\Application\Query\Work;

use App\Domain\ValueObject\Date;

class GetWorkListQuery
{
    public function __construct(
        private ?int $workTypeId = null,
        private ?string $plantationId = null,
        private ?Date $dateFrom = null,
        private ?Date $dateTo = null
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

    public function getWorkTypeId(): ?int
    {
        return $this->workTypeId;
    }

    public function getPlantationId(): ?string
    {
        return $this->plantationId;
    }
}
