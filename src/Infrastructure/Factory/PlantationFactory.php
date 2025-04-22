<?php

namespace App\Infrastructure\Factory;

use App\Domain\Entity\Plantation;
use App\Domain\Factory\PlantationFactoryInterface;
use App\Domain\ValueObject\Name;

class PlantationFactory implements PlantationFactoryInterface
{
    public function create(
        Name $name,
    ): Plantation {
        return new Plantation($name);
    }
}
