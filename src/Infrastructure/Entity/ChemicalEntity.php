<?php

namespace App\Infrastructure\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'chemicals')]
class ChemicalEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $commercialName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $activeIngredient;

    public function __construct(string $commercialName, ?string $activeIngredient)
    {
        $this->commercialName = $commercialName;
        $this->activeIngredient = $activeIngredient;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommercialName(): string
    {
        return $this->commercialName;
    }

    public function setCommercialName(string $commercialName): void
    {
        $this->commercialName = $commercialName;
    }

    public function getActiveIngredient(): ?string
    {
        return $this->activeIngredient;
    }

    public function setActiveIngredient(?string $activeIngredient): void
    {
        $this->activeIngredient = $activeIngredient;
    }
}
