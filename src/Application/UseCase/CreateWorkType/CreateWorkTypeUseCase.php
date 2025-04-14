<?php

namespace App\Application\UseCase\CreateWorkType;

use App\Domain\Enums\SystemWorkType;
use App\Domain\Factory\WorkTypeFactoryInterface;
use App\Domain\Repository\WorkTypeRepositoryInterface;
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
