<?php

namespace App\Infrastructure\Mapper;

use App\Domain\Entity\Work;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Note;
use App\Infrastructure\Entity\PlantationEntity;
use App\Infrastructure\Entity\WorkEntity;
use App\Infrastructure\Entity\WorkTypeEntity;
use Doctrine\Common\Collections\ArrayCollection;

final class WorkMapper
{
    public function __construct(
        private readonly WorkTypeMapper $workTypeMapper,
        private readonly PlantationMapper $plantationMapper,
        private readonly WorkerMapper $workerMapper,
    ) {
    }

    public function mapToDomain(WorkEntity $entity): Work
    {
        $workerEntities = $entity->getWorkers();
        $workers = [];
        foreach ($workerEntities as $workerEntity) {
            $workers[] = $this->workerMapper->mapToDomain($workerEntity);
        }
        $work = new Work(
            $this->workTypeMapper->mapToDomain($entity->getWorkType()),
            $this->plantationMapper->mapToDomain($entity->getPlantation()),
            new Date($entity->getDate()->format('Y-m-d H:i:s')),
            $workers,
            new Note($entity->getNote()),
        );

        $reflectionProperty = new \ReflectionProperty(Work::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($work, $entity->getId());
        return $work;
    }

    public function mapToEntity(
        Work $work,
        WorkTypeEntity $workType,
        PlantationEntity $plantation,
        ArrayCollection $workerEntities,
        ?WorkEntity $entity = null
    ): WorkEntity {
        $date = $work->getDate()->getValue();
        $note = $work->getNote()->getValue();
        if ($entity) {
            $entity->setWorkType($workType);
            $entity->setPlantation($plantation);
            $entity->setDate($date);
            $entity->setWorkers($workerEntities);
            $entity->setNote($note);
        } else {
            $entity = new WorkEntity(
                $workType,
                $plantation,
                $date,
                $workerEntities,
                $note
            );
        }
        return $entity;
    }
}
