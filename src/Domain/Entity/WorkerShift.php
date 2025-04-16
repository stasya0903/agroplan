<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;

class WorkerShift
{
    private ?int $id = null;
    private ?Work $work = null;

    public function __construct(
        private Worker $worker,
        private Plantation $plantation,
        private Date $date,
        private Money $payment,
        private bool $paid = false
    ) {
    }
    public function assignToWork(Work $work): void
    {
        $this->work = $work;
    }

    public function getWork(): Work
    {
        return $this->work;
    }

    public function getPlantation(): Plantation
    {
        return $this->plantation;
    }

    public function getWorker(): Worker
    {
        return $this->worker;
    }

    public function isPaid(): bool
    {
        return $this->paid;
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getDate(): Date
    {
        return $this->date;
    }

    public function getPayment(): Money
    {
        return $this->payment;
    }
}
