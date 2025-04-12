<?php

namespace App\Application\UseCase\CreateWorker;

use App\Domain\Factory\PlantationFactoryInterface;
use App\Domain\Factory\WorkerFactoryInterface;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\WorkerRepositoryInterface;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\PlantationName;
use App\Domain\ValueObject\Name;

class CreateWorkerUseCase
{
    public function __construct(
        private readonly WorkerFactoryInterface $factory,
        private readonly WorkerRepositoryInterface $repository
    ) {
    }

    public function __invoke(CreateWorkerRequest $request): CreateWorkerResponse
    {
        if ($this->repository->existsByName($request->name)) {
            throw new \DomainException('Worker name must be unique.');
        }
        $money = Money::fromFloat($request->dailyRate);
        $worker = $this->factory->create(new Name($request->name), $money);
        $this->repository->save($worker);

        return new CreateWorkerResponse($worker->getId());
    }
}
