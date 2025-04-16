<?php

namespace App\Infrastructure\Factory;

use App\Domain\Entity\Worker;
use App\Domain\Factory\WorkerFactoryInterface;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;

class WorkerFactory implements WorkerFactoryInterface
{
    public function create(Name $workerName, Money $dailyRate): Worker
    {
        return new Worker($workerName, $dailyRate);
    }
}
