<?php

namespace App\Application\UseCase\EditPlantation;

class EditPlantationResponse
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }
}
