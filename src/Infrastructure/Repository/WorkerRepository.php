<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Worker;
use App\Domain\Repository\WorkerRepositoryInterface;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;
use App\Infrastructure\Entity\PlantationEntity;
use App\Infrastructure\Entity\WorkerEntity;
use App\Infrastructure\Mapper\WorkerMapper;
use Doctrine\ORM\EntityManagerInterface;

class WorkerRepository implements WorkerRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private WorkerMapper $mapper
    )
    {
    }

    public function find(int $id): ?Worker
    {
        $entity = $this->em->getRepository(WorkerEntity::class)->find($id);
        if (!$entity) {
            return null;
        }
        return $this->mapper->mapToDomain($entity);
    }

    public function save(Worker $worker): void
    {
        $existing = $worker->getId()
            ? $this->em->getRepository(WorkerEntity::class)->findOneBy(['id' => $worker->getId()])
            : null;
        $entity = $this->mapper->mapToEntity($worker, $existing);

        $this->em->persist($entity);
        $this->em->flush();
        $reflectionProperty = new \ReflectionProperty(Worker::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($worker, $entity->getId());
    }


    public function existsByName(string $name): bool
    {
        $repository = $this->em->getRepository(WorkerEntity::class);
        $entity = $repository->findOneBy(['name' => $name]);
        return $entity !== null;
    }

    public function getList(array $ids = []): array
    {
        $query = $this->em->createQueryBuilder()
            ->select('worker')
            ->from(WorkerEntity::class, 'worker');
        if (count($ids) > 0) {
            $query = $query
                ->andWhere('worker.id IN (:ids)')
                ->setParameter('ids', $ids);
        }
        $items =  $query->getQuery()->getResult();
        $workers = [];
        foreach ($items as $item) {
            $workers[] = $this->mapper->mapToDomain($item);
        }
        return $workers;
    }
}
