<?php

namespace App\Domain\Factory;

use App\Domain\Entity\Plantation;
use App\Domain\Entity\Spending;
use App\Domain\Entity\SpendingGroup;
use App\Domain\Enums\SpendingType;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Note;

interface SpendingGroupFactoryInterface
{
    public function create(
        SpendingType $type,
        Date $date,
        Money $amount,
        Note $info,
        ?bool $isShared,
        ?array $spending = []
    ): SpendingGroup;
}
