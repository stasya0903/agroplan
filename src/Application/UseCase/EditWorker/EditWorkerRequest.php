<?php

namespace App\Application\UseCase\EditWorker;

class EditWorkerRequest
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly float $dailyRate
    ) {
    }
}
