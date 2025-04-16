<?php

namespace App\Application\UseCase\EditWork;

class EditWorkRequest
{
    public function __construct(
        public int $workTypeId,
        public int $plantationId,
        public string $date,
        public array $workerIds,
        public ?string $note
    ) {
    }
}
