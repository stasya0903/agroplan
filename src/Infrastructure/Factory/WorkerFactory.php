<?php

namespace App\Infrastructure\Factory;

use App\Domain\Entity\Plantation;
use App\Domain\Entity\Worker;
use App\Domain\Factory\PlantationFactoryInterface;
use App\Domain\Factory\WorkerFactoryInterface;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\PlantationName;
use App\Domain\ValueObject\WorkerName;

class WorkerFactory implements WorkerFactoryInterface
{
    public function create(WorkerName $workerName, Money $dailyRate): Worker
    {
        return new Worker($workerName, $dailyRate);
    }
}
