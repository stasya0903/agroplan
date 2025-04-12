<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\WorkerName;

class Worker
{
    private ?int $id = null;



    public function __construct(
        private WorkerName $name,
        private Money $dailyRate

    ) {
    }

    public function getName(): WorkerName
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

}