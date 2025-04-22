<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Incoming;
use App\Domain\Repository\IncomingRepositoryInterface;
use App\Infrastructure\Entity\PlantationEntity;
use App\Infrastructure\Entity\WorkEntity;
use App\Infrastructure\Entity\WorkerEntity;
use App\Infrastructure\Entity\IncomingEntity;
use App\Infrastructure\Mapper\IncomingMapper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

class IncomingRepository implements IncomingRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly IncomingMapper $mapper
    ) {
    }

    public function find(int $id): ?Incoming
    {
        $entity = $this->em->getRepository(IncomingEntity::class)->find($id);
        if (!$entity) {
            return null;
        }
        return $this->mapper->mapToDomain($entity);
    }

    /**
     * @throws ORMException
     */
    public function save(Incoming $incoming): void
    {
        $existing = $incoming->getId()
            ? $this->em->getRepository(IncomingEntity::class)->findOneBy(['id' => $incoming->getId()])
            : null;
        $plantationEntity = $this->em->getReference(PlantationEntity::class, $incoming->getPlantation()->getId());
        $entity = $this->mapper->mapToEntity(
            $incoming,
            $plantationEntity,
            $existing
        );

        $this->em->persist($entity);
        $this->em->flush();
        $reflectionProperty = new \ReflectionProperty(Incoming::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($incoming, $entity->getId());
    }

    public function getList(array $ids = []): array
    {
        $query = $this->em->createQueryBuilder()
            ->select('Incoming')
            ->from(IncomingEntity::class, 'Incoming');
        if (count($ids) > 0) {
            $query = $query
                ->andWhere('Incoming.id IN (:ids)')
                ->setParameter('ids', $ids);
        }
        $items =  $query->getQuery()->getResult();
        $Incoming = [];
        foreach ($items as $item) {
            $Incoming[] = $this->mapper->mapToDomain($item);
        }
        return $Incoming;
    }
    public function delete(int $incoming): void
    {
        $workerShift = $this->em->getRepository(IncomingEntity::class)->find($incoming);

        if (!$workerShift) {
            throw new \DomainException("Incoming with ID $incoming not found.");
        }

        $this->em->remove($workerShift);
        $this->em->flush();
    }
}
