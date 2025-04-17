<?php

namespace App\Application\UseCase\EditSpending;

use App\Application\DTO\SpendingDTO;
use App\Application\DTO\WorkDTO;
use App\Domain\Entity\Work;

class EditSpendingResponse
{
    public function __construct(
        public SpendingDTO $spending
    ) {
    }
}
