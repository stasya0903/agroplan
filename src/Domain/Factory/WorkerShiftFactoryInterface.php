<?php

namespace App\Domain\Factory;

use App\Domain\Entity\Plantation;
use App\Domain\Entity\Worker;
use App\Domain\Entity\WorkerShift;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;

interface WorkerShiftFactoryInterface
{
    public function create(
        Worker $worker,
        Plantation $plantation,
        Date $date,
        Money $payment,
        bool $paid =  false
    ): WorkerShift;
}
