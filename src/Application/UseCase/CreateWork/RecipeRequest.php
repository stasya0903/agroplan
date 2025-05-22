<?php

namespace App\Application\UseCase\CreateWork;

class RecipeRequest
{
    public function __construct(
        public int $chemicalId,
        public float $dosis,
        public ?int $problemId,
        public ?string $note
    ) {
    }
}
