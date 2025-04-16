<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\WorkType;
use App\Domain\Repository\WorkTypeRepositoryInterface;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;
use App\Infrastructure\Entity\PlantationEntity;
use App\Infrastructure\Entity\WorkEntity;
use App\Infrastructure\Entity\WorkTypeEntity;
use App\Infrastructure\Mapper\WorkTypeMapper;
use Doctrine\ORM\EntityManagerInterface;

class WorkTypeRepository implements WorkTypeRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private WorkTypeMapper $mapper,
    ) {
    }

    public function find(int $id): ?WorkType
    {
        $entity = $this->em->getRepository(WorkTypeEntity::class)->find($id);
        if (!$entity) {
            return null;
        }
        return $this->mapper->mapToDomain($entity);
    }

    public function save(WorkType $workType): void
    {
        $existing = $workType->getId()
            ? $this->em->getRepository(WorkEntity::class)->findOneBy(['id' => $workType->getId()])
            : null;
        $entity = $this->mapper->mapToEntity($workType, $existing);
        $this->em->persist($entity);
        $this->em->flush();
        $reflectionProperty = new \ReflectionProperty(WorkType::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($workType, $entity->getId());
    }


    public function existsByName(string $name): bool
    {
        $repository = $this->em->getRepository(WorkTypeEntity::class);
        $entity = $repository->findOneBy(['name' => $name]);
        return $entity !== null;
    }

    public function getList(array $ids = []): array
    {
        $query = $this->em->createQueryBuilder()
            ->select('workType')
            ->from(WorkTypeEntity::class, 'workType');
        if (count($ids) > 0) {
            $query = $query
                ->andWhere('workType.id IN (:ids)')
                ->setParameter('ids', $ids);
        }
        $items =  $query->getQuery()->getResult();
        $workTypes = [];
        foreach ($items as $item) {
            $workTypes[] = $this->mapper->mapToDomain($item);
        }
        return $workTypes;
    }
}
