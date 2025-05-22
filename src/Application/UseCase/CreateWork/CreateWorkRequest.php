<?php

namespace App\Application\UseCase\CreateWork;

class CreateWorkRequest
{
    public function __construct(
        public int $workTypeId,
        public int $plantationId,
        public string $date,
        public array $workerIds,
        public ?string $note,
        public  ?RecipeRequest $recipe
    ) {
    }
}
