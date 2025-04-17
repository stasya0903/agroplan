<?php

namespace App\Application\DTO;

class WorkDTO
{
    public function __construct(
        public int $id,
        public int $workTypeId,
        public string $workTypeName,
        public int $plantationId,
        public string $plantationName,
        public string $date,
        public array $workersInfo,
        public ?string $note
    ) {
    }
}