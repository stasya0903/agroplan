<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Chemical;
use App\Domain\Repository\ChemicalRepositoryInterface;
use App\Domain\ValueObject\Name;
use App\Infrastructure\Entity\ChemicalEntity;
use App\Infrastructure\Entity\IncomingEntity;
use App\Infrastructure\Mapper\ChemicalMapper;
use Doctrine\ORM\EntityManagerInterface;

class ChemicalRepository implements ChemicalRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ChemicalMapper $mapper
    ) {
    }

    public function find(int $id): ?Chemical
    {
        $entity = $this->em->getRepository(ChemicalEntity::class)->find($id);
        if (!$entity) {
            return null;
        }
        return $this->mapper->mapToDomain($entity);
    }

    public function save(Chemical $chemical): void
    {
        $existing = $chemical->getId()
            ? $this->em->getRepository(ChemicalEntity::class)->findOneBy(['id' => $chemical->getId()])
            : null;

        $entity = $this->mapper->mapToEntity($chemical, $existing);

        $this->em->persist($entity);
        $this->em->flush();
        $reflectionProperty = new \ReflectionProperty(Chemical::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($chemical, $entity->getId());
    }



    public function existsByName(string $name): bool
    {
        $repository = $this->em->getRepository(ChemicalEntity::class);
        $entity = $repository->findOneBy(['commercialName' => $name]);
        return $entity !== null;
    }

    public function getList(array $ids = []): array
    {
        $query = $this->em->createQueryBuilder()
            ->select('chemical')
            ->from(ChemicalEntity::class, 'chemical');
        if (count($ids) > 0) {
            $query = $query
                ->andWhere('chemical.id IN (:ids)')
                ->setParameter('ids', $ids);
        }
        $items =  $query->getQuery()->getResult();
        $chemicals = [];
        foreach ($items as $item) {
            $chemicals[] = $this->mapper->mapToDomain($item);
        }
        return $chemicals;
    }

    public function delete(int $chemicalId): void
    {
        $workerShift = $this->em->getRepository(ChemicalEntity::class)->find($chemicalId);

        if (!$workerShift) {
            throw new \DomainException("Chemical with ID $chemicalId not found.");
        }

        $this->em->remove($workerShift);
        $this->em->flush();
    }
}
