<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Plantation;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\ValueObject\Name;
use App\Infrastructure\Entity\PlantationEntity;
use App\Infrastructure\Mapper\PlantationMapper;
use Doctrine\ORM\EntityManagerInterface;

class PlantationRepository implements PlantationRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PlantationMapper $mapper
    ) {
    }

    public function find(int $id): ?Plantation
    {
        $entity = $this->em->getRepository(PlantationEntity::class)->find($id);
        if (!$entity) {
            return null;
        }
        return $this->mapper->mapToDomain($entity);
    }

    public function save(Plantation $plantation): void
    {

            $existing = $plantation->getId()
            ? $this->em->getRepository(PlantationEntity::class)->findOneBy(['id' => $plantation->getId()])
            : null;

        $entity = $this->mapper->mapToEntity($plantation, $existing);

        $this->em->persist($entity);
        $this->em->flush();
        $reflectionProperty = new \ReflectionProperty(Plantation::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($plantation, $entity->getId());
    }



    public function existsByName(string $name): bool
    {
        $repository = $this->em->getRepository(PlantationEntity::class);
        $entity = $repository->findOneBy(['name' => $name]);
        return $entity !== null;
    }

    public function getList(array $ids = []): array
    {
        $query = $this->em->createQueryBuilder()
            ->select('plantation')
            ->from(PlantationEntity::class, 'plantation');
        if (count($ids) > 0) {
            $query = $query
                ->andWhere('plantation.id IN (:ids)')
                ->setParameter('ids', $ids);
        }
        $items =  $query->getQuery()->getResult();
        $plantations = [];
        foreach ($items as $item) {
            $plantations[] = $this->mapper->mapToDomain($item);
        }
        return $plantations;
    }
}
