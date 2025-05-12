<?php

namespace App\Infrastructure\Mapper;

use App\Domain\Entity\Chemical;
use App\Domain\Entity\Plantation;
use App\Domain\ValueObject\Name;
use App\Infrastructure\Entity\ChemicalEntity;
use App\Infrastructure\Entity\PlantationEntity;

final class ChemicalMapper
{
    public function mapToDomain(ChemicalEntity $entity): Chemical
    {
        $chemical = new Chemical(
            new Name($entity->getCommercialName()),
            $entity->getActiveIngredient() ? new Name($entity->getActiveIngredient()) : null,
        );
        $reflectionProperty = new \ReflectionProperty(Chemical::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($chemical, $entity->getId());
        return $chemical;
    }

    public function mapToEntity(Chemical $chemical, ?ChemicalEntity $chemicalEntity = null): ChemicalEntity
    {
        $commercialName = $chemical->getCommercialName()->getValue();
        $activeIngredient = $chemical->getActiveIngredient()?->getValue() ?? null;
        if($chemicalEntity){
            $chemicalEntity->setCommercialName($commercialName);
            $chemicalEntity->setActiveIngredient($activeIngredient);
        } else {
            $chemicalEntity = new ChemicalEntity($commercialName, $activeIngredient);
        }
        return $chemicalEntity;
    }
}
