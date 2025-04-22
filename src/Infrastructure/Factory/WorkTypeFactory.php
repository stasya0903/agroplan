<?php

namespace App\Infrastructure\Factory;

use App\Domain\Entity\WorkType;
use App\Domain\Factory\WorkTypeFactoryInterface;
use App\Domain\ValueObject\Name;

class WorkTypeFactory implements WorkTypeFactoryInterface
{
    public function create(Name $workTypeName): WorkType
    {
        return new WorkType($workTypeName, false);
    }
}
