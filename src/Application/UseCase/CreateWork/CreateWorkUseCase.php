<?php

namespace App\Application\UseCase\CreateWork;

use App\Application\Shared\TransactionalSessionInterface;
use App\Domain\Enums\SpendingType;
use App\Domain\Factory\SpendingFactoryInterface;
use App\Domain\Factory\WorkerShiftFactoryInterface;
use App\Domain\Factory\WorkFactoryInterface;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\SpendingRepositoryInterface;
use App\Domain\Repository\WorkerRepositoryInterface;
use App\Domain\Repository\WorkerShiftRepositoryInterface;
use App\Domain\Repository\WorkRepositoryInterface;
use App\Domain\Repository\WorkTypeRepositoryInterface;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Note;

class CreateWorkUseCase
{
    public function __construct(
        private readonly WorkFactoryInterface $factory,
        private readonly WorkerShiftFactoryInterface $workerShiftFactory,
        private readonly WorkRepositoryInterface $repository,
        private readonly PlantationRepositoryInterface $plantationRepository,
        private readonly WorkTypeRepositoryInterface $workTypeRepository,
        private readonly WorkerRepositoryInterface $workerRepository,
        private readonly WorkerShiftRepositoryInterface $workerShiftRepository,
        private readonly SpendingFactoryInterface $spendingFactory,
        private readonly SpendingRepositoryInterface $spendingRepository,
        private readonly TransactionalSessionInterface $transaction
    ) {
    }

    public function __invoke(CreateWorkRequest $request): CreateWorkResponse
    {
        $workType = $this->workTypeRepository->find($request->workTypeId);
        if (!$workType) {
            throw new \DomainException('Work type not found.');
        }
        $plantation = $this->plantationRepository->find($request->plantationId);
        if (!$plantation) {
            throw new \DomainException('Plantation not found.');
        }

        $workers = [];
        $workerIds = $request->workerIds;
        foreach ($workerIds as $workerId) {
            $worker = $this->workerRepository->find($workerId);
            if (!$worker) {
                throw new \DomainException('Worker not found.');
            } else {
                $workers[] = $worker;
            }
        }
        return $this->transaction->run(function () use ($workers, $plantation, $workType, $request) {
            $date = new Date($request->date);
            $work = $this->factory->create(
                $workType,
                $plantation,
                $date,
                $workers,
                new Note($request->note)
            );
            $this->repository->save($work);

            $workPrice = 0;
            foreach ($workers as $worker) {
                $workerShift = $this->workerShiftFactory->create(
                    $worker,
                    $plantation,
                    $date,
                    $worker->getDailyRate()
                );
                $workerShift->assignToWork($work);
                $this->workerShiftRepository->save($workerShift);
                $workPrice += $worker->getDailyRate()->getAmount();
            }
            $spending = $this->spendingFactory->create(
                $plantation,
                SpendingType::WORK,
                $date,
                new Money($workPrice),
                new Note()
            );
            $spending->assignToWork($work);
            $this->spendingRepository->save($spending);
            return new CreateWorkResponse($work->getId());
        });
    }
}
