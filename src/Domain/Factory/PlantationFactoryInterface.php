<?php

namespace App\Domain\Factory;

use App\Domain\Entity\Plantation;
use App\Domain\ValueObject\PlantationName;

interface PlantationFactoryInterface
{
    public function create(PlantationName $name): Plantation;

}