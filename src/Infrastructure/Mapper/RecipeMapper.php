<?php

namespace App\Infrastructure\Mapper;

use App\Domain\Entity\Chemical;
use App\Domain\Entity\ProblemType;
use App\Domain\Entity\Recipe;
use App\Domain\Entity\Plantation;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Note;
use App\Domain\ValueObject\Volume;
use App\Infrastructure\Entity\ChemicalEntity;
use App\Infrastructure\Entity\ProblemTypeEntity;
use App\Infrastructure\Entity\RecipeEntity;
use App\Infrastructure\Entity\PlantationEntity;
use App\Infrastructure\Entity\WorkEntity;

final class RecipeMapper
{
    public function __construct(
        private ChemicalMapper $chemicalMapper,
        private ProblemTypeMapper $problemMapper,
    ) {
    }

    public function mapToDomain(RecipeEntity $entity): Recipe
    {
        $recipe = new Recipe(
            $this->chemicalMapper->mapToDomain($entity->getChemical()),
            new Volume($entity->getDosisInMl()),
            $entity->getProblem() ? $this->problemMapper->mapToDomain($entity->getProblem()) : null,
            $entity->getNote(),
        );
        $reflectionProperty = new \ReflectionProperty(Recipe::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($recipe, $entity->getId());
        return $recipe;
    }

    public function mapToEntity(
        Recipe $recipe,
        ChemicalEntity $chemical,
        ?ProblemTypeEntity $problem = null,
        WorkEntity $work = null,
        ?RecipeEntity $recipeEntity = null
    ): RecipeEntity
    {
        $dosis = $recipe->getDosis()->getMl();
        $note = $recipe->getNote();

        if ($recipeEntity) {
            $recipeEntity->setChemical($chemical);
            $recipeEntity->setProblem($problem);
            $recipeEntity->setDosisInMl($dosis);
            $recipeEntity->setNote($note);
            $recipeEntity->setWork($work);
        } else {
            $recipeEntity = new RecipeEntity(
                $chemical,
                $dosis,
                $work,
                $problem,
                $note
            );
        }
        return $recipeEntity;
    }
}
