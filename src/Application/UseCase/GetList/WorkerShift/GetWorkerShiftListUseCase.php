<?php

namespace App\Application\UseCase\GetList\WorkerShift;

use App\Application\Query\Work\GetWorkListHandler;
use App\Application\Query\Work\GetWorkListQuery;
use App\Application\Query\WorkShift\GetWorkerShiftListHandler;
use App\Application\Query\WorkShift\GetWorkerShiftListQuery;
use App\Application\UseCase\GetList\Work\GetWorkListRequest;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use Doctrine\DBAL\Exception;

class GetWorkerShiftListUseCase
{
    public function __construct(
        private readonly GetWorkerShiftListHandler $getNewsHandler,
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(GetWorkerShiftListRequest $request): GetWorkerShiftListResponse
    {
        $dateFrom = $request->dateFrom ? new Date($request->dateFrom . ' 00:00:00') : null;
        $dateTo = $request->dateTo ? new Date($request->dateTo . ' 23:59:59') : null;
        $query = new GetWorkerShiftListQuery(
            $request->workerId,
            $request->plantationId,
            $dateFrom,
            $dateTo,
            $request->paid
        );
        $workShifts = $this->getNewsHandler->handle($query);
        $totalToPayInCents = array_reduce($workShifts, function ($result, $item) {
            $result += Money::fromFloat($item->payment)->getAmount();
            return $result;
        }, 0);
        $totalToPay = $totalToPayInCents > 0 ? (new Money($totalToPayInCents))->getAmountAsFloat() : 0;
        return new GetWorkerShiftListResponse($workShifts, $totalToPay);
    }
}
