<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Plantation;

interface PlantationRepositoryInterface
{
    public function find(int $id): ?Plantation;
    public function save(Plantation $plantation): void;

    public function existsByName(string $name): bool;

}