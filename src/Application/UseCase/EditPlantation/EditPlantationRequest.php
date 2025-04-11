<?php

namespace App\Application\UseCase\CreatePlantation;

class EditPlantationRequest
{
    public function __construct(
        public readonly int $id,
        public readonly string $name
    ) {
    }
}
