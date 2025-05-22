<?php

namespace App\Application\DTO;

class RecipeDTO
{
    public function __construct(
        public int $id,
        public int $chemicalId,
        public string $chemicalName,
        public string $chemicalActiveIngredient,
        public ?int $problemId,
        public ?string $problemName,
        public float $dosis,
        public ?string $note = null,
    ) {
    }
}
