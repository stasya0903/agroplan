<?php

namespace App\Infrastructure\Mapper;

use App\Domain\Entity\Worker;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;
use App\Infrastructure\Entity\WorkerEntity;

final class WorkerMapper
{
    public function mapToDomain(WorkerEntity $entity): Worker
    {
        $worker = new Worker(new Name($entity->getName()), new Money($entity->getDailyRateInCents()));
        $reflectionProperty = new \ReflectionProperty(Worker::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($worker, $entity->getId());
        return $worker;
    }

    public function mapToEntity(Worker $worker, ?WorkerEntity $entity = null): WorkerEntity
    {
        $name = $worker->getName()->getValue();
        $rate = $worker->getDailyRate()->getAmount();
        if($entity){
            $entity->setName($name);
            $entity->setDailyRateInCents($rate);
        }else{
            $entity =  new WorkerEntity($name, $rate);
        }
        return $entity;
    }

}