<?php

namespace App\Application\UseCase\EditWorkerShift;

use App\Application\DTO\WorkDTO;
use App\Application\DTO\WorkerShiftDTO;
use App\Domain\Entity\Work;
use App\Domain\Entity\WorkerShift;

class EditWorkerShiftResponse
{
    public function __construct(
        public WorkerShiftDTO $workerShift,
    ) {
    }
}
