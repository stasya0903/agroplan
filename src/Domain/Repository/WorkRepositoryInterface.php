<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Work;

interface WorkRepositoryInterface
{
    public function find(int $id): ?Work;
    public function findWithAllData(int $id): ?Work;
    public function save(Work $work): void;
    public function getList(array $ids): array;
}
