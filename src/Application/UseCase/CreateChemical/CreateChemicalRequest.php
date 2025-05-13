<?php

namespace App\Application\UseCase\CreateChemical;

class CreateChemicalRequest
{
    public function __construct(
        public string $commercialName,
        public ?string $activeIngredient
    ) {
    }
}
