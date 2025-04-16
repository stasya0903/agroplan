<?php

namespace App\Infrastructure\Factory;

use App\Domain\Entity\Plantation;
use App\Domain\Entity\Spending;
use App\Domain\Enums\SpendingType;
use App\Domain\Factory\PlantationFactoryInterface;
use App\Domain\Factory\SpendingFactoryInterface;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Note;
use App\Domain\ValueObject\PlantationName;

class SpendingFactory implements SpendingFactoryInterface
{
    public function create(
        Plantation $plantation,
        SpendingType $type,
        Date $date,
        Money $amount,
        Note $info
    ): Spending
    {
        return new Spending($plantation, $type, $date, $amount, $info);
    }
}
