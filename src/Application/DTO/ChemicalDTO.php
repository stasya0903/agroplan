<?php

namespace App\Application\DTO;

class ChemicalDTO
{
    public function __construct(
        public int $id,
        public string $commercialName,
        public ?string $activeIngredient = null,
    ) {
    }
}
