<?php

namespace App\Infrastructure\Factory;

use App\Domain\Entity\Plantation;
use App\Domain\Entity\Worker;
use App\Domain\Entity\WorkType;
use App\Domain\Factory\PlantationFactoryInterface;
use App\Domain\Factory\WorkerFactoryInterface;
use App\Domain\Factory\WorkTypeFactoryInterface;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\PlantationName;
use App\Domain\ValueObject\Name;

class WorkTypeFactory implements WorkTypeFactoryInterface
{
    public function create(Name $workTypeName): WorkType
    {
        return new WorkType($workTypeName, false);
    }
}
