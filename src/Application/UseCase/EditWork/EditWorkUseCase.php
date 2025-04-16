<?php

namespace App\Application\UseCase\EditWork;

use App\Application\Shared\TransactionalSessionInterface;
use App\Application\UseCase\CreateWork\CreateWorkRequest;
use App\Application\UseCase\CreateWork\CreateWorkResponse;
use App\Domain\Entity\Work;
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

class EditWorkUseCase
{
    public function __construct(
        private readonly WorkFactoryInterface           $factory,
        private readonly WorkerShiftFactoryInterface    $workerShiftFactory,
        private readonly WorkRepositoryInterface        $repository,
        private readonly PlantationRepositoryInterface  $plantationRepository,
        private readonly WorkTypeRepositoryInterface    $workTypeRepository,
        private readonly WorkerRepositoryInterface      $workerRepository,
        private readonly WorkerShiftRepositoryInterface $workerShiftRepository,
        private readonly SpendingFactoryInterface       $spendingFactory,
        private readonly SpendingRepositoryInterface    $spendingRepository,
        private readonly TransactionalSessionInterface  $transaction
    )
    {
    }

    public function __invoke(EditWorkRequest $request): EditWorkResponse
    {
        $work = $this->repository->find($request->workId);

        if (!$work) {
            throw new \DomainException('Work not found.');
        }
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
        return $this->transaction->run(function () use ($work, $workers, $plantation, $workType, $request) {
            $date = new Date($request->date);
            $originalWorkers = $work->getWorkers();
            $work->setWorkType($workType);
            $work->setPlantation($plantation);
            $work->setDate($date);
            $work->setWorkers($workers);
            $work->setNote(new Note($request->note));

            $this->repository->save($work);

            $this->updateWorkerShifts($work, $originalWorkers);
            $this->updateSpendingForWork($work);
            return new CreateWorkResponse($work->getId());
        });
    }

    private function updateWorkerShifts(Work $work, array $originalWorkers): void
    {
        $currentWorkerIds = array_map(fn($w) => $w->getId()->value, $work->getWorkers());
        $originalWorkerIds = array_map(fn($w) => $w->getId()->value, $originalWorkers);

        $addedWorkers = array_diff($currentWorkerIds, $originalWorkerIds);
        $removedWorkers = array_diff($originalWorkerIds, $currentWorkerIds);

        // Remove WorkerShifts
        foreach ($removedWorkers as $removedId) {
            $this->workerShiftRepository->deleteByWorkAndWorkerId($work->getId(), $removedId);
        }

        // Add or update WorkerShifts
        foreach ($work->getWorkers() as $worker) {
            $workerShift = $this->workerShiftRepository->findByWorkAndWorkerId($work->getId(), $worker->getId());

            if (!$workerShift) {
                $newShift = $this->workerShiftFactory->create($work, $worker);
                $this->workerShiftRepository->save($newShift);
            } else {
                $workerShift->updateFromWork($work); // You could define a method like this
                $this->workerShiftRepository->save($workerShift);
            }
        }
    }

    private function updateSpendingForWork(\App\Domain\Entity\Work $work)
    {
    }
}
