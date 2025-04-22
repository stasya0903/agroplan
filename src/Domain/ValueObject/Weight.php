<?php

namespace App\Domain\ValueObject;

use InvalidArgumentException;

final class Weight
{
    private float $kg;

    public function __construct(float $kg)
    {
        if ($kg <= 0) {
            throw new InvalidArgumentException("Weight must be positive.");
        }

        $this->kg = round($kg, 3);
    }

    public function getKg(): float
    {
        return $this->kg;
    }

    public function getGrams(): int
    {
        return (int)round($this->kg * 1000);
    }

    public function __toString(): string
    {
        return number_format($this->kg, 3) . ' kg';
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->kg > $other->kg;
    }

    public function add(self $other): self
    {
        return new self($this->kg + $other->kg);
    }

    public static function createFromGrams(int $grams): self
    {
        $kg = round($grams / 1000);
        return new self($kg);
    }
}
