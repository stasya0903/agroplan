<?php

namespace App\Infrastructure\Factory;

use App\Domain\Entity\Incoming;
use App\Domain\Entity\Plantation;
use App\Domain\Entity\Spending;
use App\Domain\Enums\IncomingTermType;
use App\Domain\Enums\SpendingType;
use App\Domain\Factory\IncomingFactoryInterface;
use App\Domain\Factory\PlantationFactoryInterface;
use App\Domain\Factory\SpendingFactoryInterface;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Note;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Weight;

class IncomingFactory implements IncomingFactoryInterface
{
    public function create(
        Plantation $plantation,
        Date $date,
        Money $amount,
        Note $info,
        Weight $weight,
        IncomingTermType $incomingTerm,
        Name $buyerName,
        Money $price,
        ?bool $paid = false,
        ?Date $datePaid = null,
    ): Incoming {
        return new Incoming(
            $plantation,
            $date,
            $amount,
            $info,
            $weight,
            $incomingTerm,
            $buyerName,
            $price,
            $paid,
            $datePaid
        );
    }
}
