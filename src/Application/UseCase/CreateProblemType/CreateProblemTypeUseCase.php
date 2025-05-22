<?php

namespace App\Application\UseCase\CreateProblemType;

use App\Application\UseCase\CreateProblemType\CreateProblemTypeRequest;
use App\Application\UseCase\CreateProblemType\CreateProblemTypeResponse;
use App\Application\UseCase\CreateIncoming\CreateIncomingRequest;
use App\Application\UseCase\CreateIncoming\CreateIncomingResponse;
use App\Domain\Enums\IncomingTermType;
use App\Domain\Factory\ProblemTypeFactoryInterface;
use App\Domain\Factory\IncomingFactoryInterface;
use App\Domain\Repository\ProblemTypeRepositoryInterface;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\IncomingRepositoryInterface;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Note;
use App\Domain\ValueObject\Weight;

class CreateProblemTypeUseCase
{
    public function __construct(
        private readonly ProblemTypeFactoryInterface $factory,
        private readonly ProblemTypeRepositoryInterface $repository
    ) {
    }

    public function __invoke(CreateProblemTypeRequest $request): CreateProblemTypeResponse
    {
        if ($this->repository->existsByName($request->name)) {
            throw new \DomainException('ProblemType name must be unique.');
        }
        $problemType = $this->factory->create(
            new Name($request->name)
        );
        $this->repository->save($problemType);
        return new CreateProblemTypeResponse($problemType->getId());
    }
}
