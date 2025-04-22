<?php

namespace App\Application\DTO;

class WorkTypeDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public bool $isSystem
    ) {
    }
}
