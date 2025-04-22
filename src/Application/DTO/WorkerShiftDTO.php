<?php

namespace App\Application\DTO;

class WorkerShiftDTO
{
    public function __construct(
        public int $id,
        public string $date,
        public string $plantationId,
        public string $plantationName,
        public int $workerId,
        public string $workerName,
        public float $dailyRate,
        public float $payment,
        public bool $paid
    ) {
    }
}
