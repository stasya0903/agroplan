<?php

namespace App\Infrastructure\Mapper;

use App\Domain\Entity\Spending;
use App\Domain\Entity\SpendingGroup;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Note;
use App\Infrastructure\Entity\PlantationEntity;
use App\Infrastructure\Entity\SpendingGroupEntity;
use App\Infrastructure\Entity\WorkEntity;
use App\Infrastructure\Entity\WorkerEntity;
use App\Infrastructure\Entity\SpendingEntity;
use Doctrine\Common\Collections\ArrayCollection;

final class SpendingMapper
{
    public function __construct(
        private readonly PlantationMapper $plantationMapper,
        private readonly SpendingGroupMapper $spendingMapper,
    ) {
    }

    public function mapToDomain(SpendingEntity $entity): Spending
    {
        $spending = new Spending(
            $this->spendingMapper->mapToDomain($entity->getSpendingGroup()),
            $this->plantationMapper->mapToDomain($entity->getPlantation()),
            new Money($entity->getAmountInCents()),
        );
        $reflectionProperty = new \ReflectionProperty(Spending::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($spending, $entity->getId());
        return $spending;
    }

    public function mapToEntity(
        Spending $spending,
        PlantationEntity $plantation,
        SpendingEntity $entity = null,
        SpendingGroupEntity $spendingGroup = null,
    ): SpendingEntity {
        $money = $spending->getAmount()->getAmount();
        if ($entity) {
            $entity->setPlantation($plantation);
            $entity->setAmountInCents($money);
            $entity->setSpendingGroup($spendingGroup);

        } else {
            $entity = new SpendingEntity(
                $spendingGroup,
                $plantation,
                $money
            );
        }
        return $entity;
    }
}
