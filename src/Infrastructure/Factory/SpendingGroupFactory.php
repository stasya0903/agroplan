<?php

namespace App\Infrastructure\Factory;

use App\Domain\Entity\Plantation;
use App\Domain\Entity\Spending;
use App\Domain\Entity\SpendingGroup;
use App\Domain\Enums\SpendingType;
use App\Domain\Factory\PlantationFactoryInterface;
use App\Domain\Factory\SpendingFactoryInterface;
use App\Domain\Factory\SpendingGroupFactoryInterface;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Note;
use App\Domain\ValueObject\Name;

class SpendingGroupFactory implements SpendingGroupFactoryInterface
{


    public function create(
        SpendingType $type,
        Date $date,
        Money $amount,
        Note $info,
        ?bool $isShared,
        ?array $spending = []
    ): SpendingGroup {
        return new SpendingGroup($type, $date, $amount, $info, $isShared, $spending);
    }
}
