<?php

namespace App\Application\UseCase\EditWorkerShift;


use App\Application\DTO\WorkerShiftDTO;
use App\Application\Shared\TransactionalSessionInterface;
use App\Application\UseCase\EditWorkerShift\EditWorkerShiftRequest;
use App\Application\UseCase\EditWorkerShift\EditWorkerShiftResponse;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\SpendingRepositoryInterface;
use App\Domain\Repository\WorkerShiftRepositoryInterface;
use App\Domain\Repository\WorkRepositoryInterface;
use App\Domain\ValueObject\Money;

class EditWorkerShiftUseCase
{
    public function __construct(
        private readonly WorkerShiftRepositoryInterface $workerShiftRepository,
        private readonly SpendingRepositoryInterface $spendingRepository,
        private readonly WorkRepositoryInterface $workRepository,
        private readonly PlantationRepositoryInterface $plantationRepository,
        private readonly TransactionalSessionInterface $transaction
    ) {
    }

    public function __invoke(EditWorkerShiftRequest $request): EditWorkerShiftResponse
    {
        $workerShift = $this->workerShiftRepository->find($request->workerShiftId, true);
        if (!$workerShift) {
            throw new \DomainException('Worker Shift not found.');
        }

        return $this->transaction->run(function () use ($request, $workerShift) {
            $oldCost = $workerShift->getPayment()->getAmount();
            $newPayment = Money::fromFloat($request->payment);
            $newCost = $newPayment->getAmount();
            $workerShift->setPayment(Money::fromFloat($request->payment));
            $workerShift->setPaid($request->paid);
            $this->workerShiftRepository->save($workerShift);
            if($oldCost !== $newCost){
                $spending = $this->spendingRepository->findByWork($workerShift->getWork()->getId());
                $costDifference = $newCost - $oldCost;
                $currentAmount = $spending->getAmount()->getAmount();
                $updatedAmount = $currentAmount + $costDifference;
                $spending->setAmount(new Money($updatedAmount));
                $this->spendingRepository->save($spending);
            }
            return new EditWorkerShiftResponse(new WorkerShiftDTO(
                $workerShift->getId(),
                $workerShift->getDate(),
                $workerShift->getPlantation()->getId(),
                $workerShift->getPlantation()->getName()->getValue(),
                $workerShift->getWorker()->getId(),
                $workerShift->getWorker()->getName()->getValue(),
                $workerShift->getWorker()->getDailyRate()->getAmountAsFloat(),
                $workerShift->getPayment()->getAmountAsFloat(),
                $workerShift->isPaid()
            ));
        });
    }
}
