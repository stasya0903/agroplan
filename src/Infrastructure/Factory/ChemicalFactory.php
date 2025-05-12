<?php

namespace App\Infrastructure\Factory;

use App\Domain\Entity\Chemical;
use App\Domain\Factory\ChemicalFactoryInterface;
use App\Domain\ValueObject\Name;

class ChemicalFactory implements ChemicalFactoryInterface
{
    public function create(
        Name $commercialName,
        Name $activeIngredient = null
    ): Chemical {
        return new Chemical($commercialName, $activeIngredient);
    }
}
