<?php

namespace App\Application\UseCase\EditWorkerShift;

use App\Application\DTO\WorkerShiftDTO;
use App\Application\Shared\TransactionalSessionInterface;
use App\Domain\Repository\SpendingRepositoryInterface;
use App\Domain\Repository\SpendingGroupRepositoryInterface;
use App\Domain\Repository\WorkerShiftRepositoryInterface;
use App\Domain\ValueObject\Money;

class EditWorkerShiftUseCase
{
    public function __construct(
        private readonly WorkerShiftRepositoryInterface $workerShiftRepository,
        private readonly SpendingRepositoryInterface $spendingRepository,
        private readonly SpendingGroupRepositoryInterface $spendingGroupRepository,
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
            if ($oldCost !== $newCost) {
                $spendingGroup = $this->spendingGroupRepository->findByWork($workerShift->getWork()->getId());
                $costDifference = $newCost - $oldCost;
                $currentAmount = $spendingGroup->getAmount()->getAmount();
                $updatedAmount = $currentAmount + $costDifference;
                $spendingGroup->setAmount(new Money($updatedAmount));
                $this->spendingGroupRepository->save($spendingGroup);
                $spending = $this->spendingRepository->getForGroup($spendingGroup->getId());
                $spending[0]->setAmount(new Money($updatedAmount));
                $this->spendingRepository->save($spending[0]);
            }
            return new EditWorkerShiftResponse(
                new WorkerShiftDTO(
                    $workerShift->getId(),
                    $workerShift->getDate(),
                    $workerShift->getPlantation()->getId(),
                    $workerShift->getPlantation()->getName()->getValue(),
                    $workerShift->getWorker()->getId(),
                    $workerShift->getWorker()->getName()->getValue(),
                    $workerShift->getWorker()->getDailyRate()->getAmountAsFloat(),
                    $workerShift->getPayment()->getAmountAsFloat(),
                    $workerShift->isPaid()
                )
            );
        });
    }
}
