<?php

namespace App\Domain\Factory;

use App\Domain\Entity\Chemical;
use App\Domain\ValueObject\Name;

interface ChemicalFactoryInterface
{
    public function create(Name $commercialName, ?Name $activeIngredient = null): Chemical;
}
