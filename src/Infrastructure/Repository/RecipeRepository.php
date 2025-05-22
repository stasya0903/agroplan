<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Chemical;
use App\Domain\Entity\Recipe;
use App\Domain\Repository\RecipeRepositoryInterface;
use App\Domain\ValueObject\Name;
use App\Infrastructure\Entity\ChemicalEntity;
use App\Infrastructure\Entity\ProblemTypeEntity;
use App\Infrastructure\Entity\RecipeEntity;
use App\Infrastructure\Entity\IncomingEntity;
use App\Infrastructure\Entity\WorkEntity;
use App\Infrastructure\Mapper\RecipeMapper;
use Doctrine\ORM\EntityManagerInterface;

class RecipeRepository implements RecipeRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RecipeMapper $mapper
    ) {
    }

    public function find(int $id): ?Recipe
    {
        $entity = $this->em->getRepository(RecipeEntity::class)->find($id);
        if (!$entity) {
            return null;
        }
        return $this->mapper->mapToDomain($entity);
    }

    public function save(Recipe $recipe): void
    {
        $existing = $recipe->getId()
            ? $this->em->getRepository(RecipeEntity::class)->findOneBy(['id' => $recipe->getId()])
            : null;

        $workEntity = $recipe->getWork()
            ? $this->em->getReference(WorkEntity::class, $recipe->getWork()->getId())
            : null;
        $chemical = $this->em->getReference(ChemicalEntity::class, $recipe->getChemical()->getId());
        $problem = $recipe->getProblem()
            ? $this->em->getReference(ProblemTypeEntity::class, $recipe->getProblem()->getId())
            : null;
        $entity = $this->mapper->mapToEntity($recipe, $chemical, $problem, $workEntity, $existing);

        $this->em->persist($entity);
        $this->em->flush();
        $reflectionProperty = new \ReflectionProperty(Recipe::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($recipe, $entity->getId());
    }

    public function getList(array $ids = []): array
    {
        $query = $this->em->createQueryBuilder()
            ->select('recipe')
            ->from(RecipeEntity::class, 'recipe');
        if (count($ids) > 0) {
            $query = $query
                ->andWhere('recipe.id IN (:ids)')
                ->setParameter('ids', $ids);
        }
        $items = $query->getQuery()->getResult();
        $recipes = [];
        foreach ($items as $item) {
            $recipes[] = $this->mapper->mapToDomain($item);
        }
        return $recipes;
    }

    public function delete(int $recipeId): void
    {
        $workerShift = $this->em->getRepository(RecipeEntity::class)->find($recipeId);

        if (!$workerShift) {
            throw new \DomainException("Recipe with ID $recipeId not found.");
        }

        $this->em->remove($workerShift);
        $this->em->flush();
    }

    public function findByWork(int $workId): ?Recipe
    {
        $entity = $this->em->getRepository(RecipeEntity::class)
            ->findOneBy(['work' => $workId]);
        if (!$entity) {
            return null;
        }
        return $this->mapper->mapToDomain($entity);
    }
}
