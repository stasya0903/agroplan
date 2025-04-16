<?php

namespace App\Infrastructure\Mapper;

use App\Domain\Entity\WorkType;
use App\Domain\ValueObject\Name;
use App\Infrastructure\Entity\WorkTypeEntity;

final class WorkTypeMapper
{
    public function mapToDomain(WorkTypeEntity $entity): WorkType
    {
        $workType = new WorkType(
            new Name($entity->getName()),
            $entity->isSystem()
        );
        $reflectionProperty = new \ReflectionProperty(WorkType::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($workType, $entity->getId());
        return $workType;
    }

    public function mapToEntity(WorkType $workType, ?WorkTypeEntity $entity = null): WorkTypeEntity
    {
        $id = $workType->getId();
        $name = $workType->getName()->getValue();
        $isSystem = $workType->isSystem();
        if ($entity) {
            $entity->setId($id);
            $entity->setName($name);
            $entity->setIsSystem($isSystem);
        } else {
            $entity = $existing ?? new WorkTypeEntity($name, $isSystem);
        }
        return $entity;
    }
}
