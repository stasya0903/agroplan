<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\ProblemType;
use App\Domain\Repository\ProblemTypeRepositoryInterface;
use App\Domain\ValueObject\Name;
use App\Infrastructure\Entity\ProblemTypeEntity;
use App\Infrastructure\Entity\IncomingEntity;
use App\Infrastructure\Mapper\ProblemTypeMapper;
use Doctrine\ORM\EntityManagerInterface;

class ProblemTypeRepository implements ProblemTypeRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProblemTypeMapper $mapper
    ) {
    }

    public function find(int $id): ?ProblemType
    {
        $entity = $this->em->getRepository(ProblemTypeEntity::class)->find($id);
        if (!$entity) {
            return null;
        }
        return $this->mapper->mapToDomain($entity);
    }

    public function save(ProblemType $problemType): void
    {
        $existing = $problemType->getId()
            ? $this->em->getRepository(ProblemTypeEntity::class)->findOneBy(['id' => $problemType->getId()])
            : null;

        $entity = $this->mapper->mapToEntity($problemType, $existing);

        $this->em->persist($entity);
        $this->em->flush();
        $reflectionProperty = new \ReflectionProperty(ProblemType::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($problemType, $entity->getId());
    }



    public function existsByName(string $name): bool
    {
        $repository = $this->em->getRepository(ProblemTypeEntity::class);
        $entity = $repository->findOneBy(['name' => $name]);
        return $entity !== null;
    }

    public function getList(array $ids = []): array
    {
        $query = $this->em->createQueryBuilder()
            ->select('problemType')
            ->from(ProblemTypeEntity::class, 'problemType');
        if (count($ids) > 0) {
            $query = $query
                ->andWhere('problemType.id IN (:ids)')
                ->setParameter('ids', $ids);
        }
        $items =  $query->getQuery()->getResult();
        $problemTypes = [];
        foreach ($items as $item) {
            $problemTypes[] = $this->mapper->mapToDomain($item);
        }
        return $problemTypes;
    }

    public function delete(int $problemTypeId): void
    {
        $workerShift = $this->em->getRepository(ProblemTypeEntity::class)->find($problemTypeId);

        if (!$workerShift) {
            throw new \DomainException("ProblemType with ID $problemTypeId not found.");
        }

        $this->em->remove($workerShift);
        $this->em->flush();
    }
}
