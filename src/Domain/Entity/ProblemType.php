<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\Name;

class ProblemType
{
    private ?int $id = null;

    public function __construct(
        private Name $name
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function rename(Name $newName): void
    {
        $this->name = $newName;
    }
}
