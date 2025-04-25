<?php

namespace App\Domain\Repository;

use App\Domain\Entity\SpendingGroup;

interface SpendingGroupRepositoryInterface
{
    public function find(int $id): ?SpendingGroup;
    public function save(SpendingGroup $spending): void;
    public function getList(array $ids): array;
    public function delete(int $spendingId);

    public function findByWork(int $workId): ?SpendingGroup;
}
