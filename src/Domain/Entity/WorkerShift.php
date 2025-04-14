<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\Money;

class WorkerShift
{
    private ?int $id = null;
    public function __construct(
        private int $workerId,
        private \DateTimeInterface $date,
        private Money $payment,
        private bool $paid = false

    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWorkerId(): int
    {
        return $this->workerId;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function getPayment(): Money
    {
        return $this->payment;
    }

}