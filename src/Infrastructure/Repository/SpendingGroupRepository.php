<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\SpendingGroup;
use App\Domain\Repository\SpendingGroupRepositoryInterface;
use App\Infrastructure\Entity\PlantationEntity;
use App\Infrastructure\Entity\WorkEntity;
use App\Infrastructure\Entity\WorkerEntity;
use App\Infrastructure\Entity\SpendingGroupEntity;
use App\Infrastructure\Mapper\SpendingGroupMapper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

class SpendingGroupRepository implements SpendingGroupRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SpendingGroupMapper $mapper
    ) {
    }

    public function find(int $id): ?SpendingGroup
    {
        $entity = $this->em->getRepository(SpendingGroupEntity::class)->find($id);
        if (!$entity) {
            return null;
        }
        return $this->mapper->mapToDomain($entity);
    }
    public function findByWork(int $workId): ?SpendingGroup
    {
        $entity = $this->em->getRepository(SpendingGroupEntity::class)
            ->findOneBy(['work' => $workId]);
        if (!$entity) {
            return null;
        }
        return $this->mapper->mapToDomain($entity);
    }

    /**
     * @throws ORMException
     */
    public function save(SpendingGroup $spending): void
    {
        $existing = $spending->getId()
            ? $this->em->getRepository(SpendingGroupEntity::class)->findOneBy(['id' => $spending->getId()])
            : null;
        $workEntity = $spending->getWork()
            ? $this->em->getReference(WorkEntity::class, $spending->getWork()->getId())
            : null;
        $entity = $this->mapper->mapToEntity(
            $spending,
            $workEntity,
            $existing
        );

        $this->em->persist($entity);
        $this->em->flush();
        $reflectionProperty = new \ReflectionProperty(SpendingGroup::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($spending, $entity->getId());
    }

    public function getList(array $ids = []): array
    {
        $query = $this->em->createQueryBuilder()
            ->select('spending')
            ->from(SpendingGroupEntity::class, 'spending_group');
        if (count($ids) > 0) {
            $query = $query
                ->andWhere('spending.id IN (:ids)')
                ->setParameter('ids', $ids);
        }
        $items =  $query->getQuery()->getResult();
        $spending = [];
        foreach ($items as $item) {
            $spending[] = $this->mapper->mapToDomain($item);
        }
        return $spending;
    }
    public function delete(int $spendingId): void
    {
        $workerShift = $this->em->getRepository(SpendingGroupEntity::class)->find($spendingId);

        if (!$workerShift) {
            throw new \DomainException("SpendingGroup with ID $spendingId not found.");
        }

        $this->em->remove($workerShift);
        $this->em->flush();
    }
}
