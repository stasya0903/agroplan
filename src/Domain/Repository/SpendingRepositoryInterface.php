<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Spending;

interface SpendingRepositoryInterface
{
    public function find(int $id): ?Spending;
    public function save(Spending $spending): void;

    public function getList(array $ids): array;
    public function delete(int $spendingId);

    public function getForGroup(int $groupId, array $except = []): array;

    public function deleteForGroup(int $groupId): void;
}
