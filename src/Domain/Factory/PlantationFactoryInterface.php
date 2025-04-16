<?php

namespace App\Domain\Factory;

use App\Domain\Entity\Plantation;
use App\Domain\ValueObject\Name;

interface PlantationFactoryInterface
{
    public function create(Name $name): Plantation;
}
