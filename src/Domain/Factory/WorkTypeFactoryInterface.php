<?php

namespace App\Domain\Factory;

use App\Domain\Entity\WorkType;
use App\Domain\ValueObject\Name;

interface WorkTypeFactoryInterface
{
    public function create(Name $workTypeName): WorkType;
}
