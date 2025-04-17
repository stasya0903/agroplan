<?php

namespace App\Application\UseCase\SetPaidWorkerShifts;

class SetPaidWorkerShiftsResponse
{
    public function __construct(
        public bool $result
    ) {
    }
}
