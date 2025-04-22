<?php

namespace App\Application\UseCase\EditWork;

use App\Application\DTO\WorkDTO;
use App\Domain\Entity\Work;

class EditWorkResponse
{
    public function __construct(
        public WorkDTO $work
    ) {
    }
}
