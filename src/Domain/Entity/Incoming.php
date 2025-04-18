<?php

namespace App\Domain\Entity;

use App\Domain\Enums\IncomingTermType;
use App\Domain\Enums\SpendingType;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Note;
use App\Domain\ValueObject\Weight;

class Incoming
{
    private ?int $id = null;

    public function __construct(
        private Plantation $plantation,
        private Date $date,
        private Money $amount,
        private Note $info,
        private Weight $weight,
        private IncomingTermType $incomingTerm,
        private Name $buyerName,
        private Money $price,
        private ?bool $paid = false,
        private ?Date $datePaid = null,
    ) {

    }

    public function getDatePaid(): ?Date
    {
        return $this->datePaid;
    }

    public function isPaid(): bool
    {
        return $this->paid;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlantation(): Plantation
    {
        return $this->plantation;
    }

    public function getDate(): Date
    {
        return $this->date;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getInfo(): Note
    {
        return $this->info;
    }

    public function getWeight(): Weight
    {
        return $this->weight;
    }

    public function getIncomingTerm(): IncomingTermType
    {
        return $this->incomingTerm;
    }

    public function getBuyerName(): Name
    {
        return $this->buyerName;
    }

    public function setPaid(Date $datePaid): void
    {
        $this->datePaid = $datePaid;
        $this->paid = true;
    }
}
