<?php

namespace App\Application\UseCase\CreateSpending;

class CreateSpendingResponse
{
    public function __construct(
        public int $id
    ) {
    }
}
