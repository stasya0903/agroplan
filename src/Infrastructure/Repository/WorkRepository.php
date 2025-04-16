<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Work;
use App\Domain\Repository\WorkRepositoryInterface;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;
use App\Infrastructure\Entity\PlantationEntity;
use App\Infrastructure\Entity\WorkEntity;
use App\Infrastructure\Entity\WorkerEntity;
use App\Infrastructure\Entity\WorkTypeEntity;
use App\Infrastructure\Mapper\WorkMapper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

class WorkRepository implements WorkRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly WorkMapper $mapper
    ) {
    }

    public function find(int $id): ?Work
    {
        $entity = $this->em->getRepository(WorkEntity::class)->find($id);
        if (!$entity) {
            return null;
        }
        return $this->mapper->mapToDomain($entity);
    }

    /**
     * @throws ORMException
     */
    public function save(Work $work): void
    {
        $existing = $work->getId()
            ? $this->em->getRepository(WorkEntity::class)->findOneBy(['id' => $work->getId()])
            : null;
        $workTypeEntity = $this->em->getReference(WorkTypeEntity::class, $work->getWorkType()->getId());
        $plantationEntity = $this->em->getReference(PlantationEntity::class, $work->getPlantation()->getId());

        $workerEntities = new ArrayCollection();
        foreach ($work->getWorkers() as $worker) {
            $workerEntities->add($this->em->getReference(WorkerEntity::class, $worker->getId()));
        }
        $entity = $this->mapper->mapToEntity(
            $work,
            $workTypeEntity,
            $plantationEntity,
            $workerEntities,
            $existing
        );

        $this->em->persist($entity);
        $this->em->flush();
        $reflectionProperty = new \ReflectionProperty(Work::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($work, $entity->getId());
    }
    public function getList(array $ids = []): array
    {
        $query = $this->em->createQueryBuilder()
            ->select('work')
            ->from(WorkEntity::class, 'work');
        if (count($ids) > 0) {
            $query = $query
                ->andWhere('work.id IN (:ids)')
                ->setParameter('ids', $ids);
        }
        $items =  $query->getQuery()->getResult();
        $works = [];
        foreach ($items as $item) {
            $works[] = $this->mapper->mapToDomain($item);
        }
        return $works;
    }
}
