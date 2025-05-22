<?php

namespace App\Application\DTO;

class ProblemTypeDTO
{
    public function __construct(
        public int $id,
        public string $name
    ) {
    }
}
