<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Plantation;
use App\Domain\Entity\Worker;

interface WorkerRepositoryInterface
{
    public function find(int $id): ?Worker;
    public function save(Worker $worker): void;
    public function existsByName(string $name): bool;
    public function getList(array $ids): array;
}
