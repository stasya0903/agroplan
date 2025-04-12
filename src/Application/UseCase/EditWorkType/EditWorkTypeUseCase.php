<?php

namespace App\Application\UseCase\EditWorkType;

use App\Domain\Factory\WorkTypeFactoryInterface;
use App\Domain\Repository\WorkTypeRepositoryInterface;
use App\Domain\SystemWorkType;
use App\Domain\ValueObject\Name;

class EditWorkTypeUseCase
{
    public function __construct(
        private readonly WorkTypeRepositoryInterface $repository
    ) {
    }

    public function __invoke(EditWorkTypeRequest $request): EditWorkTypeResponse
    {
        if (SystemWorkType::isSystemId($request->id) ) {
            throw new \DomainException('Can not edit system work type');
        }
        if (SystemWorkType::isSystemName($request->name) ) {
            throw new \DomainException('WorkType name used by system.');
        }
        $workType = $this->repository->find($request->id);

        if (!$workType) {
            throw new \DomainException('WorkType not found.');
        }
        if ( $this->repository->existsByName($request->name) &&
            $request->name !== $workType->getName()) {
            throw new \DomainException('WorkType name must be unique.');
        }
        $workType->rename(new Name($request->name));
        $this->repository->save($workType);

        return new EditWorkTypeResponse($workType->getId(), $workType->getName()->getValue());
    }
}
