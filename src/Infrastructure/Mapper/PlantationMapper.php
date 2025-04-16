<?php

namespace App\Infrastructure\Mapper;

use App\Domain\Entity\Plantation;
use App\Domain\ValueObject\PlantationName;
use App\Infrastructure\Entity\PlantationEntity;

final class PlantationMapper
{
    public function mapToDomain(PlantationEntity $entity): Plantation
    {
        $plantation = new Plantation(new PlantationName($entity->getName()));
        $reflectionProperty = new \ReflectionProperty(Plantation::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($plantation, $entity->getId());
        return $plantation;
    }

    public function mapToEntity(Plantation $plantation, ?PlantationEntity $plantationEntity = null): PlantationEntity
    {
        $entity = $plantationEntity ?? new PlantationEntity($plantation->getName()->getValue());
        $entity->setName($plantation->getName()->getValue());
        return $entity;
    }
}
