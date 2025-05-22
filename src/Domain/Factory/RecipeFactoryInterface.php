<?php

namespace App\Domain\Factory;

use App\Domain\Entity\Chemical;
use App\Domain\Entity\ProblemType;
use App\Domain\Entity\Recipe;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Volume;

interface RecipeFactoryInterface
{
    public function create(
        Chemical $chemical,
        Volume $dosis,
        ?ProblemType $problem,
        ?string $note
    ): Recipe;
}
