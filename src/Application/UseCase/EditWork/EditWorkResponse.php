<?php

namespace App\Application\UseCase\EditWork;

use App\Domain\Entity\Work;

class EditWorkResponse
{
    public function __construct(
        public Work $work
    ) {
    }
}
