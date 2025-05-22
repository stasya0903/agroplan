<?php

namespace App\Domain\ValueObject;

use InvalidArgumentException;

final class Volume
{
    private float $ml;

    public function __construct(float $ml)
    {
        if ($ml <= 0) {
            throw new InvalidArgumentException("Volume must be positive.");
        }

        $this->ml = $ml;
    }

    public function getMl(): float
    {
        return $this->ml;
    }

    public function equals(Volume $getDosis)
    {
    }
}
