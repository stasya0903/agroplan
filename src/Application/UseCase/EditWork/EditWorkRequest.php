<?php

namespace App\Application\UseCase\EditWork;

use App\Application\UseCase\CreateWork\RecipeRequest;

class EditWorkRequest
{
    public function __construct(
        public int $workId,
        public int $workTypeId,
        public int $plantationId,
        public string $date,
        public array $workerIds,
        public ?string $note,
        public  ?RecipeRequest $recipe
    ) {
    }
}
