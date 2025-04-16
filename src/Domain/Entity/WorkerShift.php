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

    public function setWorker(Worker $worker): void
    {
        $this->worker = $worker;
    }

    public function setPlantation(Plantation $plantation): void
    {
        $this->plantation = $plantation;
    }

    public function setDate(Date $date): void
    {
        $this->date = $date;
    }

    public function setPayment(Money $payment): void
    {
        $this->payment = $payment;
    }

    public function setPaid(bool $paid): void
    {
        $this->paid = $paid;
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
