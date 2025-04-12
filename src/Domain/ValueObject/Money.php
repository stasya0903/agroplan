<?php

namespace App\Domain\ValueObject;

final class Money
{
    private int $amountInCents;

    public function __construct(
        int $amountInCents,
    ) {
        $this->assertValidAmount($amountInCents);
        $this->amountInCents = $amountInCents;
    }

    public function getAmount(): int
    {
        return $this->amountInCents;
    }
    public function add(Money $other): Money
    {
        return new self($this->amountInCents + $other->amountInCents);
    }
    public static function fromFloat(float $amount): self
    {
        return new self((int) round($amount * 100));
    }

    public function getAmountAsFloat(): float
    {
        return $this->amountInCents / 100;
    }

    private function assertValidAmount(int $amount): void
    {
        if (!$amount) {
            throw new \InvalidArgumentException('Amount cannot be empty');
        }
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than zero.');
        }


    }
}