<?php

namespace App\Infrastructure\Factory;

use App\Domain\Entity\ProblemType;
use App\Domain\Factory\ProblemTypeFactoryInterface;
use App\Domain\ValueObject\Name;

class ProblemTypeFactory implements ProblemTypeFactoryInterface
{
    public function create(
        Name $name
    ): ProblemType {
        return new ProblemType($name);
    }
}
