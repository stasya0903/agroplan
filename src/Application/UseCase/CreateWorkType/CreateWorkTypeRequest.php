<?php

namespace App\Application\UseCase\CreateWorkType;

class CreateWorkTypeRequest
{
    public function __construct(
        public readonly string $name
    ) {
    }
}
