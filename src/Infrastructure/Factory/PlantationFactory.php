<?php

namespace App\Infrastructure\Factory;

use App\Domain\Entity\Plantation;
use App\Domain\Factory\PlantationFactoryInterface;
use App\Domain\ValueObject\PlantationName;

class PlantationFactory implements PlantationFactoryInterface
{
    public function create(
        PlantationName $name,
    ): Plantation {
        return new Plantation($name);
    }
}
