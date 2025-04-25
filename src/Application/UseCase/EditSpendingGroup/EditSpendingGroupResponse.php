<?php

namespace App\Application\UseCase\EditSpendingGroup;

use App\Application\DTO\SpendingDTO;
use App\Application\DTO\SpendingGroupDTO;
use App\Application\DTO\WorkDTO;
use App\Domain\Entity\Work;

class EditSpendingGroupResponse
{
    public function __construct(
        public SpendingGroupDTO $spendingGroup
    ) {
    }
}
