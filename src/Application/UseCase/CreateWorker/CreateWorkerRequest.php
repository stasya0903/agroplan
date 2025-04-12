<?php

namespace App\Application\UseCase\CreateWorker;

class CreateWorkerRequest
{
    public function __construct(
        public readonly string $name,
        public readonly float $dailyRate
    ) {
    }
}
