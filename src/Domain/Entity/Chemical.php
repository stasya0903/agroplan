<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\Name;

class Chemical
{
    private ?int $id = null;

    public function __construct(
        private Name $commercialName,
        private ?Name $activeIngredient
    ) {
    }

    public function getCommercialName(): Name
    {
        return $this->commercialName;
    }

    public function getActiveIngredient(): ?Name
    {
        return $this->activeIngredient;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setCommercialName(Name $commercialName): void
    {
        $this->commercialName = $commercialName;
    }

    public function setActiveIngredient(?Name $activeIngredient): void
    {
        $this->activeIngredient = $activeIngredient;
    }
}
