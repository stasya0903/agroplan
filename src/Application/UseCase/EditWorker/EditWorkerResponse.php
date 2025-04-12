<?php

namespace App\Application\UseCase\EditWorker;

class EditWorkerResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public readonly float $dailyRate
    ) {
    }
}
