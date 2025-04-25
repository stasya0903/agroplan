<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Spending;
use App\Domain\Repository\SpendingRepositoryInterface;
use App\Infrastructure\Entity\PlantationEntity;
use App\Infrastructure\Entity\SpendingGroupEntity;

use App\Infrastructure\Entity\SpendingEntity;
use App\Infrastructure\Mapper\SpendingMapper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

class SpendingRepository implements SpendingRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SpendingMapper $mapper,
    ) {
    }

    public function find(int $id, $withGroup = true): ?Spending
    {
        $qb = $this->em->createQueryBuilder()
            ->select('s')
            ->from(SpendingEntity::class, 's')
            ->where('s.id = :id')
            ->setParameter('id', $id);

        if ($withGroup) {
            $qb->addSelect('sg')
                ->leftJoin('s.spendingGroup', 'sg');
        }

        $entity = $qb->getQuery()->getOneOrNullResult();
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
        $groupEntity = $spending->getSpendingGroup()
            ? $this->em->getReference(SpendingGroupEntity::class, $spending->getSpendingGroup()->getId())
            : null;
        $entity = $this->mapper->mapToEntity(
            $spending,
            $plantationEntity,
            $existing,
            $groupEntity
        );

        $this->em->persist($entity);
        $this->em->flush();
        $reflectionProperty = new \ReflectionProperty(Spending::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($spending, $entity->getId());
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
        $items = $query->getQuery()->getResult();
        $spending = [];
        foreach ($items as $item) {
            $spending[] = $this->mapper->mapToDomain($item);
        }
        return $spending;
    }

    public function delete(int $spendingId): void
    {
        $spending = $this->em->getRepository(SpendingEntity::class)->find($spendingId);

        if (!$spending) {
            throw new \DomainException("Spending with ID $spendingId not found.");
        }

        $this->em->remove($spending);
        $this->em->flush();
    }

    public function getForGroup(int $groupId, array $except = []): array
    {
        $qry = $this->em->createQueryBuilder()
            ->select('s')
            ->from(SpendingEntity::class, 's')
            ->leftJoin('s.spendingGroup', 'sg')->addSelect('sg')
            ->where('s.spendingGroup = (:group_id)')
            ->setParameter('group_id', $groupId);

        if (count($except) > 0) {
            $qry->andWhere('s.id NOT IN (:ids)')
                ->setParameter('ids', $except);
        }

        $items = $qry->getQuery()->getResult();
        $spending = [];
        foreach ($items as $item) {
            $spending[] = $this->mapper->mapToDomain($item);
        }
        return $spending;
    }

    public function deleteForGroup(int $groupId): void
    {
        $this->em->createQueryBuilder()
            ->select('s')
            ->from(SpendingEntity::class, 's')
            ->where('s.group_id = :group_id')
            ->setParameter('group_id', $groupId)
            ->delete()
            ->getQuery()
            ->getResult();
    }
}
