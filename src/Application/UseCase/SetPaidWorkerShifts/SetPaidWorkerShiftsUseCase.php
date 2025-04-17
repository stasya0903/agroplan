<?php

namespace App\Application\UseCase\SetPaidWorkerShifts;

use App\Application\Query\Work\GetWorkListHandler;
use App\Application\Query\Work\GetWorkListQuery;
use App\Application\Query\WorkShift\GetWorkerShiftListHandler;
use App\Application\Query\WorkShift\GetWorkerShiftListQuery;
use App\Application\UseCase\GetList\Work\GetWorkListRequest;
use App\Application\UseCase\GetList\WorkerShift\GetWorkerShiftListRequest;
use App\Application\UseCase\GetList\WorkerShift\GetWorkerShiftListResponse;
use App\Domain\Repository\WorkerShiftRepositoryInterface;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Infrastructure\Repository\WorkerShiftRepository;
use Doctrine\DBAL\Exception;
use PHPUnit\Event\InvalidArgumentException;

class SetPaidWorkerShiftsUseCase
{
    public function __construct(
        private readonly WorkerShiftRepositoryInterface $workerShiftRepository,
    ) {
    }

    /**
     * @param SetPaidWorkerShiftsRequest $request
     * @return SetPaidWorkerShiftsResponse
     */
    public function __invoke(SetPaidWorkerShiftsRequest $request): SetPaidWorkerShiftsResponse
    {
        if(count($request->workerShiftIds) === 0){
            throw new InvalidArgumentException('Chose worker shifts to be paid');
        }
        foreach ($request->workerShiftIds as $id) {
            if (!is_int($id)) {
                throw new \InvalidArgumentException("All worker shift IDs must be integers.");
            }
        }
        return new SetPaidWorkerShiftsResponse($this->workerShiftRepository->setPaid($request->workerShiftIds));
    }
}
