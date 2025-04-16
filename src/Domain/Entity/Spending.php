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
        private Plantation $plantation,
        private SpendingType $type,
        private Date $date,
        private Money $amount,
        private Note $info
    ) {
    }

    public function setPlantation(Plantation $plantation): void
    {
        $this->plantation = $plantation;
    }

    public function setType(SpendingType $type): void
    {
        $this->type = $type;
    }

    public function setDate(Date $date): void
    {
        $this->date = $date;
    }

    public function setAmount(Money $amount): void
    {
        $this->amount = $amount;
    }

    public function setInfo(Note $info): void
    {
        $this->info = $info;
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

    public function getType(): SpendingType
    {
        return $this->type;
    }

    public function getDate(): Date
    {
        return $this->date;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getInfo(): ?Note
    {
        return $this->info;
    }
}
