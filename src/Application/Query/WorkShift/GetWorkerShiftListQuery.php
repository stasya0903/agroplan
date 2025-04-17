<?php

namespace App\Application\Query\WorkShift;

use App\Domain\ValueObject\Date;

class GetWorkerShiftListQuery
{
    public function __construct(
        private ?int $workerId = null,
        private ?int $plantationId = null,
        private ?Date $dateFrom = null,
        private ?Date $dateTo = null,
        private ?bool $paid = null
    ) {
    }

    public function getWorkerId(): ?int
    {
        return $this->workerId;
    }

    public function getPaid(): ?bool
    {
        return $this->paid;
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
