<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Incoming;

interface IncomingRepositoryInterface
{
    public function find(int $id): ?Incoming;
    public function save(Incoming $incoming): void;
    public function getList(array $ids): array;
    public function delete(int $incoming);
}
