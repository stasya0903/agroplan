<?php

namespace App\Domain\ValueObject;

class WorkerName
{
    private string $value;

    public function __construct(?string $value)
    {
        $this->assertValidName($value);
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    private function assertValidName(?string $value): void
    {
        if (!$value) {
            throw new \InvalidArgumentException('Name cannot be empty');
        }
    }
}
