<?php

namespace App\Infrastructure\Mapper;

use App\Domain\Entity\Spending;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Note;
use App\Infrastructure\Entity\PlantationEntity;
use App\Infrastructure\Entity\WorkEntity;
use App\Infrastructure\Entity\WorkerEntity;
use App\Infrastructure\Entity\SpendingEntity;
use Doctrine\Common\Collections\ArrayCollection;

final class SpendingMapper
{
    public function __construct(
        private readonly PlantationMapper $plantationMapper
    ) {}

    public function mapToDomain(SpendingEntity $entity): Spending
    {
        $spending = new Spending(
            $this->plantationMapper->mapToDomain($entity->getPlantation()),
            $entity->getType(),
            new Date($entity->getDate()->format('Y-m-d H:i:s')),
            new Money($entity->getAmountInCents()),
            new Note($entity->getNote()),
        );
        $reflectionProperty = new \ReflectionProperty(Spending::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($spending, $entity->getId());
        return $spending;
    }

    public function mapToEntity(Spending $spending,
                                PlantationEntity $plantation,
                                ?WorkEntity $work,
                                SpendingEntity $entity = null
    ): SpendingEntity {
        $date = $spending->getDate()->getValue();
        $money = $spending->getAmount()->getAmount();
        $type = $spending->getType();
        $note = $spending->getInfo()->getValue();
        
        if ($entity) {
            $entity->setPlantation($plantation);
            $entity->setType($type);
            $entity->setDate($date);
            $entity->setAmountInCents($money);
            $entity->setNote($note);

        } else {
            $entity = new SpendingEntity(
                $plantation,
                $type,
                $date,
                $money,
                $note
            );
        }
        if ($work !== null) {
            $entity->setWork($work);
        }
        return $entity;
    }



}