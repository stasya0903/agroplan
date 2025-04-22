<?php

namespace App\Application\UseCase\EditWorkType;

class EditWorkTypeResponse
{
    public function __construct(
        public int $id,
        public string $name
    ) {
    }
}
