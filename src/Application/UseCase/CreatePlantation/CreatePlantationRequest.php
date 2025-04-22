<?php

namespace App\Application\UseCase\CreatePlantation;

class CreatePlantationRequest
{
    public function __construct(
        public readonly string $name
    ) {
    }
}
