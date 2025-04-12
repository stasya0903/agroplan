<?php

namespace App\Application\UseCase\EditWorker;

use App\Domain\Repository\WorkerRepositoryInterface;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;

class EditWorkerUseCase
{
    public function __construct(
        private readonly WorkerRepositoryInterface $workerRepository
    ) {
    }

    public function __invoke(EditWorkerRequest $request): EditWorkerResponse
    {
        $worker = $this->workerRepository->find($request->id);

        if (!$worker) {
            throw new \DomainException('Worker not found.');
        }

        if (
            $this->workerRepository->existsByName($request->name) &&
            $request->name !== $worker->getName()
        ) {
            throw new \DomainException('Worker name must be unique.');
        }

        $worker->rename(new Name($request->name));
        $worker->setDailyRate(Money::fromFloat($request->dailyRate));
        $this->workerRepository->save($worker);

        return new EditWorkerResponse(
            $worker->getId(),
            $worker->getName()->getValue(),
            $worker->getDailyRate()->getAmountAsFloat()
        );
    }
}
