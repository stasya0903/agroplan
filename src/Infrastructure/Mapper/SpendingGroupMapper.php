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

final class SpendingGroupMapper
{
    public function __construct(
        private readonly WorkMapper $workMapper
    ) {
    }

    public function mapToDomain(SpendingGroupEntity $entity): SpendingGroup
    {
        $spending = new SpendingGroup(
            $entity->getType(),
            new Date($entity->getDate()->format('Y-m-d H:i:s')),
            new Money($entity->getAmountInCents()),
            new Note($entity->getNote()),
            $entity->getIsShared(),
        );
        $reflectionProperty = new \ReflectionProperty(SpendingGroup::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($spending, $entity->getId());
        if ($entity->getWork()) {
            $spending->assignToWork($this->workMapper->mapToDomain($entity->getWork()));
        }
        return $spending;
    }

    public function mapToEntity(
        SpendingGroup $spending,
        ?WorkEntity $work = null,
        ?SpendingGroupEntity $entity = null,
    ): SpendingGroupEntity {
        $date = $spending->getDate()->getValue();
        $money = $spending->getAmount()->getAmount();
        $type = $spending->getType();
        $note = $spending->getInfo()->getValue();
        if ($entity) {
            $entity->setType($type);
            $entity->setDate($date);
            $entity->setAmountInCents($money);
            $entity->setNote($note);
            $entity->setIsShared($spending->isShared());
        } else {
            $entity = new SpendingGroupEntity(
                $type,
                $date,
                $money,
                $note,
                $spending->isShared()
            );
        }
        if ($work !== null) {
            $entity->setWork($work);
        }
        return $entity;
    }
}
