<?php

namespace App\Application\UseCase\CreateIncoming;

class CreateIncomingResponse
{
    public function __construct(
        public int $id
    ) {
    }
}
