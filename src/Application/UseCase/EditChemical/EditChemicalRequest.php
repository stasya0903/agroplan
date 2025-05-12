<?php

namespace App\Application\UseCase\EditChemical;

class EditChemicalRequest
{
    public function __construct(
        public readonly int $id,
        public readonly string $commercialName,
        public readonly ?string $activeIngredient,
    ) {
    }
}
