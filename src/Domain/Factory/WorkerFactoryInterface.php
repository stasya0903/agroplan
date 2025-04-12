<?php

namespace App\Domain\Factory;

use App\Domain\Entity\Plantation;
use App\Domain\Entity\Worker;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\PlantationName;
use App\Domain\ValueObject\WorkerName;

interface WorkerFactoryInterface
{
    public function create(WorkerName $workerName, Money $dailyRate): Worker;
}
