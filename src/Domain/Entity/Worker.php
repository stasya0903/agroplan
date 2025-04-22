<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;

class Worker
{
    private ?int $id = null;

    public function __construct(
        private Name $name,
        private Money $dailyRate
    ) {
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function getDailyRate(): Money
    {
        return $this->dailyRate;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function rename(Name $param): void
    {
        $this->name = $param;
    }

    public function setDailyRate(Money $dailyRate): void
    {
        $this->dailyRate = $dailyRate;
    }
}
