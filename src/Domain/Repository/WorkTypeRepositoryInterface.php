<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Plantation;
use App\Domain\Entity\Worker;
use App\Domain\Entity\WorkType;

interface WorkTypeRepositoryInterface
{
    public function find(int $id): ?WorkType;
    public function save(WorkType $workType): void;
    public function existsByName(string $name): bool;
    public function getList(array $ids): array;
}
