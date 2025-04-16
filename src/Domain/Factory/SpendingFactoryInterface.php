<?php

namespace App\Domain\Factory;

use App\Domain\Entity\Plantation;
use App\Domain\Entity\Spending;
use App\Domain\Enums\SpendingType;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Note;

interface SpendingFactoryInterface
{
    public function create(Plantation $plantation,
                           SpendingType $type,
                           Date $date,
                           Money $amount,
                           Note $info)
    : Spending;
}
