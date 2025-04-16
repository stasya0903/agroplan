<?php

namespace App\Infrastructure\Mapper;

use App\Domain\Entity\WorkerShift;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Note;
use App\Infrastructure\Entity\PlantationEntity;
use App\Infrastructure\Entity\WorkEntity;
use App\Infrastructure\Entity\WorkerEntity;
use App\Infrastructure\Entity\WorkerShiftEntity;
use Doctrine\Common\Collections\ArrayCollection;

final class WorkerShiftMapper
{
    public function __construct(
        private readonly PlantationMapper $plantationMapper,
        private readonly WorkerMapper $workerMapper,
    ) {
    }

    public function mapToDomain(WorkerShiftEntity $entity): WorkerShift
    {
        $workerShift = new WorkerShift(
            $this->workerMapper->mapToDomain($entity->getWorker()),
            $this->plantationMapper->mapToDomain($entity->getPlantation()),
            new Date($entity->getDate()->format('Y-m-d H:i:s')),
            new Money($entity->getPaymentInCents()),
            $entity->isPaid()
        );
        $reflectionProperty = new \ReflectionProperty(WorkerShift::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($workerShift, $entity->getId());
        return $workerShift;
    }

    public function mapToEntity(
        WorkerShift $workerShift,
        WorkerEntity $worker,
        PlantationEntity $plantation,
        WorkEntity $work,
        WorkerShiftEntity $entity = null
    ): WorkerShiftEntity {
        $date = $workerShift->getDate()->getValue();
        $money = $workerShift->getPayment()->getAmount();
        $paid = $workerShift->isPaid();
        if ($entity) {
            $entity->setWorker($worker);
            $entity->setPlantation($plantation);
            $entity->setDate($date);
            $entity->setPaymentInCents($money);
            $entity->setPaid($paid);
            $entity->setWork($work);
        } else {
            $entity = new WorkerShiftEntity(
                $worker,
                $plantation,
                $work,
                $date,
                $money,
                $paid
            );
        }
        return $entity;
    }
}
