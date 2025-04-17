<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\WorkerShift;
use App\Domain\Repository\WorkerShiftRepositoryInterface;
use App\Infrastructure\Entity\PlantationEntity;
use App\Infrastructure\Entity\WorkEntity;
use App\Infrastructure\Entity\WorkerEntity;
use App\Infrastructure\Entity\WorkerShiftEntity;
use App\Infrastructure\Mapper\WorkerShiftMapper;
use App\Infrastructure\Mapper\WorkMapper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

class WorkerShiftRepository implements WorkerShiftRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly WorkerShiftMapper $mapper,
        private readonly WorkMapper $workMapper,
    ) {
    }

    public function find(int $id, $withWork = false): ?WorkerShift
    {
        $qb = $this->em->createQueryBuilder()
            ->select('ws')
            ->from(WorkerShiftEntity::class, 'ws')
            ->where('ws.id = :id')
            ->setParameter('id', $id);

        if ($withWork) {
            $qb->addSelect('w')
                ->leftJoin('ws.work', 'w');
        }

        $entity = $qb->getQuery()->getOneOrNullResult();

        if (!$entity) {
            return null;
        }
        $workerShift = $this->mapper->mapToDomain($entity);
        if($withWork){
            $workEntity = $entity->getWork();
            $workerShift->assignToWork($this->workMapper->mapToDomain($workEntity));
        }

        return $workerShift;
    }
    public function findByWork(int $workId): array
    {
        $items = $this->em->createQueryBuilder()
            ->select('workerShift')
            ->from(WorkerShiftEntity::class, 'workerShift')
            ->leftJoin('workerShift.work', 'w')->addSelect('w')
            ->andWhere('workerShift.work = (:id)')
            ->setParameter('id', $workId)
            ->getQuery()
            ->getResult();

        $workerShifts = [];
        foreach ($items as $item) {
            $workEntity = $item->getWork();
            $workerShift = $this->mapper->mapToDomain($item);
            $workerShift->assignToWork($this->workMapper->mapToDomain($workEntity));
            $workerShifts[] = $workerShift;
        }

        return $workerShifts;
    }

    /**
     * @throws ORMException
     */
    public function save(WorkerShift $workerShift): void
    {
        $existing = $workerShift->getId()
            ? $this->em->getRepository(WorkerShiftEntity::class)->findOneBy(['id' => $workerShift->getId()])
            : null;
        $plantationEntity = $this->em->getReference(PlantationEntity::class, $workerShift->getPlantation()->getId());
        $workerEntity = $this->em->getReference(WorkerEntity::class, $workerShift->getWorker()->getId());
        $workEntity = $workerShift->getWork()
            ? $this->em->getReference(WorkEntity::class, $workerShift->getWork()->getId())
            : null;
        $entity = $this->mapper->mapToEntity(
            $workerShift,
            $workerEntity,
            $plantationEntity,
            $workEntity,
            $existing
        );

        $this->em->persist($entity);
        $this->em->flush();
        $reflectionProperty = new \ReflectionProperty(WorkerShift::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($workerShift, $entity->getId());
    }


    public function existsByName(string $name): bool
    {
        $repository = $this->em->getRepository(WorkerShiftEntity::class);
        $entity = $repository->findOneBy(['name' => $name]);
        return $entity !== null;
    }

    public function getList(array $ids = []): array
    {
        $query = $this->em->createQueryBuilder()
            ->select('workerShift')
            ->from(WorkerShiftEntity::class, 'workerShift');
        if (count($ids) > 0) {
            $query = $query
                ->andWhere('workerShift.id IN (:ids)')
                ->setParameter('ids', $ids);
        }
        $items =  $query->getQuery()->getResult();
        $workerShifts = [];
        foreach ($items as $item) {
            $workerShifts[] = $this->mapper->mapToDomain($item);
        }
        return $workerShifts;
    }

    public function delete(int $workerShiftId): void
    {
        $workerShift = $this->em->getRepository(WorkerShiftEntity::class)->find($workerShiftId);

        if (!$workerShift) {
            throw new \DomainException("WorkerShift with ID $workerShiftId not found.");
        }

        $this->em->remove($workerShift);
        $this->em->flush();
    }
}
