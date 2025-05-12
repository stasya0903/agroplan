<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Chemical;

interface ChemicalRepositoryInterface
{
    public function find(int $id): ?Chemical;
    public function save(Chemical $chemical): void;
    public function existsByName(string $name): bool;
    public function getList(array $ids): array;
    public function delete(int $chemicalId): void;
}
