<?php

namespace App\Infrastructure\Factory;

use App\Domain\Entity\Plantation;
use App\Domain\Entity\Worker;
use App\Domain\Entity\WorkerShift;
use App\Domain\Factory\PlantationFactoryInterface;
use App\Domain\Factory\WorkerShiftFactoryInterface;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\PlantationName;

class WorkerShiftFactory implements WorkerShiftFactoryInterface
{
    public function create(
        Worker $worker,
        Plantation $plantation,
        Date $date,
        Money $payment,
        bool $paid = false
    ): WorkerShift
    {
        return new WorkerShift($worker, $plantation, $date, $payment, $paid);
    }
}
