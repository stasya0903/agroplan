<?php

namespace App\Application\DTO;

class WorkerDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public float $dailyRate
    ) {
    }
}
