<?php

namespace App\Domain\Factory;

use App\Domain\Entity\Plantation;
use App\Domain\Entity\Worker;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Name;

interface WorkerFactoryInterface
{
    public function create(Name $workerName, Money $dailyRate): Worker;
}
