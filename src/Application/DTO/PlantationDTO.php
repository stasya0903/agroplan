<?php

namespace App\Application\DTO;

class PlantationDTO
{
    public function __construct(
        public int $id,
        public string $name
    ) {
    }


}