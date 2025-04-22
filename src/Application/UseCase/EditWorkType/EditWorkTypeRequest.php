<?php

namespace App\Application\UseCase\EditWorkType;

class EditWorkTypeRequest
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {
    }
}
