<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Worker;
use App\Domain\Repository\WorkerRepositoryInterface;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;
use App\Infrastructure\Entity\PlantationEntity;
use App\Infrastructure\Entity\WorkerEntity;
use Doctrine\ORM\EntityManagerInterface;

class WorkerRepository implements WorkerRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function find(int $id): ?Worker
    {
        $entity = $this->em->getRepository(WorkerEntity::class)->find($id);
        if (!$entity) {
            return null;
        }
        return $this->mapToDomain($entity);
    }

    public function save(Worker $worker): void
    {
        $entity = $this->mapToEntity($worker);

        $this->em->persist($entity);
        $this->em->flush();
        $reflectionProperty = new \ReflectionProperty(Worker::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($worker, $entity->getId());
    }

    private function mapToDomain(WorkerEntity $entity): Worker
    {
        $worker = new Worker(new Name($entity->getName()), new Money($entity->getDailyRateInCents()));
        $reflectionProperty = new \ReflectionProperty(Worker::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($worker, $entity->getId());
        return $worker;
    }

    private function mapToEntity(Worker $worker): WorkerEntity
    {
        $id = $worker->getId();
        $name = $worker->getName()->getValue();
        $rate = $worker->getDailyRate()->getAmount();
        if ($id) {
            $entity = $this->em->getRepository(WorkerEntity::class)->findOneBy(['id' => $worker->getId()]);
            $entity->setName($name);
            $entity->setDailyRateInCents($rate);
        } else {
            $entity = $existing ?? new WorkerEntity($name, $rate);
        }
        return $entity;
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
            $workers[] = $this->mapToDomain($item);
        }
        return $workers;
    }
}
