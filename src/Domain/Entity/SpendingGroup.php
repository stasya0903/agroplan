<?php

namespace App\Domain\Entity;

use App\Domain\Enums\SpendingType;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Note;

class SpendingGroup
{
    private ?int $id = null;
    private ?Work $work = null;

    public function __construct(
        private SpendingType $type,
        private Date $date,
        private Money $amount,
        private Note $info,
        private bool $isShared = false,
        private ?array $spending = []
    ) {
        $this->validate();
    }

    public function getSpending(): array
    {
        return $this->spending;
    }

    public function setSpending(array $spending): void
    {
        $this->spending = $spending;
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
    private function validate(): void
    {
        if ($this->type === SpendingType::OTHER && !$this->info->getValue()) {
            throw new \DomainException('Note is required for OTHER spending type.');
        }
    }

    public function isShared(): bool
    {
        return $this->isShared;
    }

    public function setShared(bool $isShared): void
    {
        $this->isShared = $isShared;
    }

    public function addSpending(Spending $spending): void
    {
        $this->spending[] = $spending;
    }
}
