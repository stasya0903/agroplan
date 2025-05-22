<?php

namespace App\Application\UseCase\CreateProblemType;

class CreateProblemTypeRequest
{
    public function __construct(
        public string $name
    ) {
    }
}
