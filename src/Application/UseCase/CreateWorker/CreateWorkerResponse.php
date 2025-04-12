<?php

namespace App\Application\UseCase\CreateWorker;

class CreateWorkerResponse
{
    public function __construct(
        public int $id
    ) {
    }
}
