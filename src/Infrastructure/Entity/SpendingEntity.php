<?php

namespace App\Infrastructure\Entity;

use App\Domain\Entity\SpendingGroup;
use App\Domain\Entity\Work;
use App\Domain\Enums\SpendingType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'spending')]
class SpendingEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PlantationEntity::class)]
    #[ORM\JoinColumn(nullable: false)]
    private PlantationEntity $plantation;

    #[ORM\ManyToOne(targetEntity: SpendingGroupEntity::class)]
    #[ORM\JoinColumn(nullable: false)]
    private SpendingGroupEntity $spendingGroup;


    public function getSpendingGroup(): SpendingGroupEntity
    {
        return $this->spendingGroup;
    }

    public function setSpendingGroup(SpendingGroupEntity $spendingGroup): void
    {
        $this->spendingGroup = $spendingGroup;
    }

    #[ORM\Column(type: 'integer')]
    private int $amountInCents;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getPlantation(): PlantationEntity
    {
        return $this->plantation;
    }

    public function setPlantation(PlantationEntity $plantation): void
    {
        $this->plantation = $plantation;
    }

    public function getAmountInCents(): int
    {
        return $this->amountInCents;
    }

    public function setAmountInCents(int $amountInCents): void
    {
        $this->amountInCents = $amountInCents;
    }

    public function __construct(
        SpendingGroupEntity $spendingGroup,
        PlantationEntity $plantation,
        int $payment
    ) {
        $this->plantation = $plantation;
        $this->amountInCents = $payment;
        $this->spendingGroup = $spendingGroup;
    }
}
