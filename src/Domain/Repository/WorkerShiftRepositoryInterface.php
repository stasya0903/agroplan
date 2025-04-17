<?php

namespace App\Domain\Repository;

use App\Domain\Entity\WorkerShift;

interface WorkerShiftRepositoryInterface
{
    public function find(int $id, $withWork = false): ?WorkerShift;
    public function save(WorkerShift $workerShift): void;
    public function getList(array $ids): array;

    public function findByWork(int $workId): array;

    public function delete(int $workerShiftId): void;

    public function setPaid(array $workerShiftIds) : bool;
}
