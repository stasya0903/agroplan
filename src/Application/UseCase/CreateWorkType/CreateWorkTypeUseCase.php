<?php

namespace App\Application\UseCase\CreateWorkType;

use App\Application\UseCase\CreateWorkType\CreateWorkTypeRequest;
use App\Application\UseCase\CreateWorkType\CreateWorkTypeResponse;
use App\Domain\Factory\PlantationFactoryInterface;
use App\Domain\Factory\WorkTypeFactoryInterface;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\WorkTypeRepositoryInterface;
use App\Domain\SystemWorkType;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\PlantationName;
use App\Domain\ValueObject\Name;

class CreateWorkTypeUseCase
{
    public function __construct(
        private readonly WorkTypeFactoryInterface $factory,
        private readonly WorkTypeRepositoryInterface $repository
    ) {
    }

    public function __invoke(CreateWorkTypeRequest $request): CreateWorkTypeResponse
    {
        if (SystemWorkType::isSystemName($request->name)) {
            throw new \DomainException('WorkType name used by system.');
        }
        if ($this->repository->existsByName($request->name)) {
            throw new \DomainException('WorkType name must be unique.');
        }
        $workType = $this->factory->create(new Name($request->name));
        $this->repository->save($workType);

        return new CreateWorkTypeResponse($workType->getId());
    }
}
