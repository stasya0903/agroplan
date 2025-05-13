<?php

namespace App\Application\UseCase\EditChemical;

class EditChemicalResponse
{
    public function __construct(
        public int $id,
        public string $commercialName,
        public ?string $activeIngredient,
    ) {
    }
}
