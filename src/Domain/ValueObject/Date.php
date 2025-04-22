<?php

namespace App\Domain\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;

final class Date
{
    private DateTimeImmutable $value;

    public function __construct(string $date)
    {
        $format = 'Y-m-d H:i:s';
        $parsed = DateTimeImmutable::createFromFormat($format, $date);

        if (!$parsed) {
            throw new InvalidArgumentException("Invalid date format. Expected: $format");
        }
        $this->value = $parsed;
    }

    public function getValue(): DateTimeImmutable
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value->format('Y-m-d H:i:s');
    }
}
