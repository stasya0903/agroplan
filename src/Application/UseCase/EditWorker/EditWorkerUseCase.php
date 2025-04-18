<?php

namespace App\Application\UseCase\EditWorker;

use App\Application\Shared\TransactionalSessionInterface;
use App\Domain\Repository\WorkerRepositoryInterface;
use App\Domain\Repository\WorkerShiftRepositoryInterface;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;

class EditWorkerUseCase
{
    public function __construct(
        private readonly WorkerRepositoryInterface $workerRepository,
        private readonly WorkerShiftRepositoryInterface $workerShiftRepository,
        private readonly TransactionalSessionInterface $transaction,
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

        return $this->transaction->run(function () use ($worker, $request) {
            $worker->rename(new Name($request->name));
            $oldDailyRate = $worker->getDailyRate()->getAmount();
            $worker->setDailyRate(Money::fromFloat($request->dailyRate));
            $this->workerRepository->save($worker);

            $newDailyRate = $worker->getDailyRate()->getAmount();
            if ($oldDailyRate !== $newDailyRate) {
                $shifts = $this->workerShiftRepository->getForWorker($worker->getId());
                foreach ($shifts as $shift) {
                    if (!$shift->isPaid() && $shift->getPayment()->getAmount() === $oldDailyRate) {
                        $shift->setPayment(new Money($newDailyRate));
                        $this->workerShiftRepository->save($shift);
                    }
                }
            }
            return new EditWorkerResponse(
                $worker->getId(),
                $worker->getName()->getValue(),
                $worker->getDailyRate()->getAmountAsFloat()
            );
        });
    }
}
