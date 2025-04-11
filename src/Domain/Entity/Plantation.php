<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\PlantationName;

class Plantation
{
    private ?int $id = null;

    public function __construct(
        private  PlantationName $name
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): PlantationName
    {
        return $this->name;
    }

    public function rename(PlantationName $newName): void
    {
        $this->name = $newName;
    }
}
