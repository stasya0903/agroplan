<?php

namespace App\Application\UseCase\EditIncoming;

use App\Application\DTO\IncomingDTO;
use App\Application\DTO\WorkDTO;
use App\Domain\Entity\Work;

class EditIncomingResponse
{
    public function __construct(
        public IncomingDTO $incoming
    ) {
    }
}
