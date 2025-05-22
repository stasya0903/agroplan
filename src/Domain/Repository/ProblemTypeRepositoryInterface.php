<?php

namespace App\Domain\Repository;

use App\Domain\Entity\ProblemType;

interface ProblemTypeRepositoryInterface
{
    public function find(int $id): ?ProblemType;
    public function save(ProblemType $problemType): void;
    public function existsByName(string $name): bool;
    public function getList(array $ids): array;
    public function delete(int $problemTypeId): void;
}
