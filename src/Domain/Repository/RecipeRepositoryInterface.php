<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Recipe;
use App\Domain\Entity\SpendingGroup;

interface RecipeRepositoryInterface
{
    public function find(int $id): ?Recipe;
    public function save(Recipe $recipe): void;

    public function getList(array $ids): array;
    public function delete(int $recipeId);

    public function findByWork(int $workId): ?Recipe;
}
