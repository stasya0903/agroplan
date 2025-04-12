<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Plantation;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\ValueObject\PlantationName;
use App\Infrastructure\Entity\PlantationEntity;
use Doctrine\ORM\EntityManagerInterface;

class PlantationRepository implements PlantationRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function find(int $id): ?Plantation
    {
        $entity = $this->em->getRepository(PlantationEntity::class)->find($id);
        if (!$entity) {
            return null;
        }
        return $this->mapToDomain($entity);
    }

    public function save(Plantation $plantation): void
    {
        $entity = $this->mapToEntity($plantation);

        $this->em->persist($entity);
        $this->em->flush();
        $reflectionProperty = new \ReflectionProperty(Plantation::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($plantation, $entity->getId());
    }

    private function mapToDomain(PlantationEntity $entity): Plantation
    {
        $plantation = new Plantation(new PlantationName($entity->getName()));
        $reflectionProperty = new \ReflectionProperty(Plantation::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($plantation, $entity->getId());
        return $plantation;
    }

    private function mapToEntity(Plantation $plantation): PlantationEntity
    {
        $id = $plantation->getId();
        if ($id) {
            $existing = $this->em
                ->getRepository(PlantationEntity::class)
                ->findOneBy(['id' => $plantation->getId()]);
        }
        $entity = $existing ?? new PlantationEntity($plantation->getName()->getValue());
        $entity->setName($plantation->getName()->getValue());
        return $entity;
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
            $plantations[] = $this->mapToDomain($item);
        }
        return $plantations;
    }
}
