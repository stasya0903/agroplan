<?php

namespace App\Domain\Entity;

use App\Domain\Enums\SpendingType;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Note;

class Spending
{
    private ?int $id = null;
    private ?Work $work = null;

    public function __construct(
        private SpendingGroup $spendingGroup,
        private Plantation $plantation,
        private Money $amount
    ) {
    }

    public function getSpendingGroup(): SpendingGroup
    {
        return $this->spendingGroup;
    }

    public function setSpendingGroup(SpendingGroup $spendingGroup): void
    {
        $this->spendingGroup = $spendingGroup;
    }

    public function setPlantation(Plantation $plantation): void
    {
        $this->plantation = $plantation;
    }


    public function setAmount(Money $amount): void
    {
        $this->amount = $amount;
    }

    public function setInfo(Note $info): void
    {
        $this->info = $info;
        $this->validate();
    }

    public function getWork(): ?Work
    {
        return $this->work;
    }

    public function assignToWork(Work $work): void
    {
        $this->work = $work;
    }

    public function isLinkedToWork(): bool
    {
        return $this->work !== null;
    }
    public function getPlantation(): Plantation
    {
        return $this->plantation;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }
}
