<?php

namespace App\Application\UseCase\EditWork;

use App\Application\DTO\WorkDTO;
use App\Application\DTO\WorkerDTO;
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
        private readonly WorkerShiftFactoryInterface    $workerShiftFactory,
        private readonly WorkRepositoryInterface        $repository,
        private readonly PlantationRepositoryInterface  $plantationRepository,
        private readonly WorkTypeRepositoryInterface    $workTypeRepository,
        private readonly WorkerRepositoryInterface      $workerRepository,
        private readonly WorkerShiftRepositoryInterface $workerShiftRepository,
        private readonly SpendingRepositoryInterface    $spendingRepository,
        private readonly TransactionalSessionInterface  $transaction
    )
    {
    }

    public function __invoke(EditWorkRequest $request): EditWorkResponse
    {
        $work = $this->repository->findWithShiftsAndSpending($request->workId);

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

            $work->setWorkType($workType);
            $work->setPlantation($plantation);
            $work->setDate($date);
            $work->setWorkers($workers);
            $work->setNote(new Note($request->note));
            $this->repository->save($work);

            $this->updateWorkerShifts($work);
            $this->updateSpendingForWork($work);
            $workersDto = [];
            $workers = $work->getWorkers();
            foreach ($workers as $worker) {
                $workersDto[] = new WorkerDto(
                    $worker->getId(),
                    $worker->getName()->getValue(),
                    $worker->getDailyRate()->getAmountAsFloat()
                );
            }
            return new EditWorkResponse(new WorkDto(
                $work->getWorkType()->getId(),
                $work->getWorkType()->getName()->getValue(),
                $work->getPlantation()->getId(),
                $work->getPlantation()->getName()->getValue(),
                $work->getDate(),
                $workersDto,
                $work->getNote()->getValue()
            ));
        });
    }

    private function updateWorkerShifts(Work $work): void
    {
        $originalShifts = $work->getWorkerShifts();
        $workers = $work->getWorkers();
        $newWorkerIds = array_map(fn($w) => $w->getId(), $workers);

        foreach ($originalShifts as $shift) {
            $work->removeWorkerShift($shift);
            if (!in_array($shift->getWorker()->getId(), $newWorkerIds)) {
                $this->workerShiftRepository->delete($shift->getId());
            } else {
                $shift->setPlantation($work->getPlantation());
                $shift->setDate($work->getDate());
                $this->workerShiftRepository->save($shift);
                $work->addWorkerShift($shift);
            }
        }
        $shiftWorkerIds = array_map(fn($s) => $s->getWorker()->getId(), $work->getWorkerShifts());

        foreach ($workers as $worker) {
            if (!in_array($worker->getId(),  $shiftWorkerIds)) {
                $shift = $this->workerShiftFactory->create(
                    $worker,
                    $work->getPlantation(),
                    $work->getDate(),
                    $worker->getDailyRate()
                );
                $work->addWorkerShift($shift);
                $this->workerShiftRepository->save($shift);
            }
        }
    }

    private function updateSpendingForWork(Work $work): void
    {
        $spending = $work->getSpending();
        $spending->setPlantation($work->getPlantation());
        $spending->setDate($work->getDate());
        $spending->setAmount($work->getFullPrice());
        $this->spendingRepository->save($spending);
        $work->assignSpending($spending);
    }
}
