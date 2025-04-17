<?php

namespace App\Application\UseCase\GetList\WorkerShift;

class GetWorkerShiftListResponse
{
    public function __construct(
        public iterable $workerShifts,
        public float $totalToPay
    ) {
    }
}
