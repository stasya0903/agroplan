<?php

namespace App\Infrastructure\Seeder;

use App\Domain\Entity\WorkType;
use App\Domain\Enums\SystemWorkType;
use App\Domain\Repository\WorkTypeRepositoryInterface;
use App\Domain\ValueObject\Name;

class SystemWorkTypeSeeder
{
    public function __construct(
        private readonly WorkTypeRepositoryInterface $workTypeRepository
    ) {
    }

    public function seed(): void
    {
        foreach (SystemWorkType::cases() as $type) {
            $existingWorkType = $this->workTypeRepository->find($type->value);

            if ($existingWorkType) {
                $existingWorkType->rename(new Name($type->label()));
                $this->workTypeRepository->save($existingWorkType);
            } else {
                $newWorkType = new WorkType(new Name($type->label()));
                $reflectionProperty = new \ReflectionProperty(WorkType::class, 'id');
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($newWorkType, $type->value);
                $this->workTypeRepository->save($newWorkType);
            }
        }
    }
}
