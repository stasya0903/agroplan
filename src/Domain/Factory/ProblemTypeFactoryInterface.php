<?php

namespace App\Domain\Factory;

use App\Domain\Entity\ProblemType;
use App\Domain\ValueObject\Name;

interface ProblemTypeFactoryInterface
{
    public function create(Name $name): ProblemType;
}
