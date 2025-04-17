<?php

namespace App\Application\UseCase\SetPaidWorkerShifts;

class SetPaidWorkerShiftsRequest
{
    public function __construct(
        public array $workerShiftIds
    ) {
    }
}
