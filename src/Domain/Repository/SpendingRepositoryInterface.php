<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Spending;

interface SpendingRepositoryInterface
{
    public function find(int $id): ?Spending;
    public function save(Spending $spending): void;
    public function findByWork(int $workId): ?Spending;
    public function getList(array $ids): array;
}
