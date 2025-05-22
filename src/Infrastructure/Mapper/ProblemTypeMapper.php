<?php

namespace App\Infrastructure\Mapper;

use App\Domain\Entity\ProblemType;
use App\Domain\Entity\Plantation;
use App\Domain\ValueObject\Name;
use App\Infrastructure\Entity\ProblemTypeEntity;
use App\Infrastructure\Entity\PlantationEntity;

final class ProblemTypeMapper
{
    public function mapToDomain(ProblemTypeEntity $entity): ProblemType
    {
        $problemType = new ProblemType(new Name($entity->getName()));
        $reflectionProperty = new \ReflectionProperty(ProblemType::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($problemType, $entity->getId());
        return $problemType;
    }

    public function mapToEntity(ProblemType $problemType, ?ProblemTypeEntity $problemTypeEntity = null): ProblemTypeEntity
    {
        $name = $problemType->getName()->getValue();
        if ($problemTypeEntity) {
            $problemTypeEntity->setName($name);
        } else {
            $problemTypeEntity = new ProblemTypeEntity($name);
        }
        return $problemTypeEntity;
    }
}
