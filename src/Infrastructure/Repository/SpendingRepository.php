<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Spending;
use App\Domain\Repository\SpendingRepositoryInterface;
use App\Infrastructure\Entity\PlantationEntity;
use App\Infrastructure\Entity\WorkEntity;
use App\Infrastructure\Entity\WorkerEntity;
use App\Infrastructure\Entity\SpendingEntity;
use App\Infrastructure\Mapper\SpendingMapper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

class SpendingRepository implements SpendingRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SpendingMapper $mapper
    )
    {
    }

    public function find(int $id): ?Spending
    {
        $entity = $this->em->getRepository(SpendingEntity::class)->find($id);
        if (!$entity) {
            return null;
        }
        return $this->mapper->mapToDomain($entity);
    }
    public function findByWork(int $workId): ?Spending
    {
        $entity = $this->em->getRepository(SpendingEntity::class)->findOneBy(['work_id' => $workId]);
        if (!$entity) {
            return null;
        }
        return $this->mapper->mapToDomain($entity);
    }

    /**
     * @throws ORMException
     */
    public function save(Spending $spending): void
    {
        $existing = $spending->getId()
            ? $this->em->getRepository(SpendingEntity::class)->findOneBy(['id' => $spending->getId()])
            : null;
        $plantationEntity = $this->em->getReference(PlantationEntity::class, $spending->getPlantation()->getId());
        $workEntity = $spending->getWork()
            ? $this->em->getReference(WorkEntity::class, $spending->getWork()->getId())
            : null;
        $entity = $this->mapper->mapToEntity(
            $spending,
            $plantationEntity,
            $workEntity,
            $existing
        );

        $this->em->persist($entity);
        $this->em->flush();
        $reflectionProperty = new \ReflectionProperty(Spending::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($spending, $entity->getId());
    }


    public function existsByName(string $name): bool
    {
        $repository = $this->em->getRepository(SpendingEntity::class);
        $entity = $repository->findOneBy(['name' => $name]);
        return $entity !== null;
    }

    public function getList(array $ids = []): array
    {
        $query = $this->em->createQueryBuilder()
            ->select('spending')
            ->from(SpendingEntity::class, 'spending');
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


}
