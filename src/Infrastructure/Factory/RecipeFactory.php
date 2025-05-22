<?php

namespace App\Infrastructure\Factory;

use App\Domain\Entity\Chemical;
use App\Domain\Entity\ProblemType;
use App\Domain\Entity\Recipe;
use App\Domain\Factory\RecipeFactoryInterface;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Volume;

class RecipeFactory implements RecipeFactoryInterface
{
    public function create(Chemical $chemical, Volume $dosis, ?ProblemType $problem, ?string $note): Recipe
    {
        return new Recipe($chemical, $dosis, $problem, $note);
    }
}
