<?php

namespace App\Application\UseCase\EditWorkerShift;

class EditWorkerShiftRequest
{
    public function __construct(
        public int $workerShiftId,
        public float $payment,
        public bool $paid
    ) {
    }
}
