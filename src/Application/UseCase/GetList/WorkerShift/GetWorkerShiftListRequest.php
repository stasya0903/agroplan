<?php

namespace App\Application\UseCase\GetList\WorkerShift;

class GetWorkerShiftListRequest
{
    public function __construct(
        public ?int $workerId = null,
        public ?int $plantationId = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public ?bool $paid = null
    ) {
    }
}
