<?php

namespace App\Application\DTO;

class WorkDTO
{
    public function __construct(
        public int $workTypeId,
        public string $workTypeName,
        public int $plantationId,
        public string $date,
        public array $workerIds,
        public ?string $note
    ) {
    }

}