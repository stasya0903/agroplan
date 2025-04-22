<?php

namespace App\Application\UseCase\GetBudget;

use App\Application\Query\Incoming\GetIncomingListHandler;
use App\Application\Query\Incoming\GetIncomingListQuery;
use App\Application\Query\Spending\GetSpendingListHandler;
use App\Application\Query\Spending\GetSpendingListQuery;
use App\Application\UseCase\GetList\Incoming\GetIncomingListRequest;
use App\Application\UseCase\GetList\Incoming\GetIncomingListResponse;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use Doctrine\DBAL\Exception;

class GetBudgetUseCase
{
    public function __construct(
        private readonly GetIncomingListHandler $getIncomingListHandler,
        private readonly GetSpendingListHandler $getSpendingListHandler,
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(GetBudgetRequest $request): GetBudgetResponse
    {
        $dateFrom = $request->dateFrom ? new Date($request->dateFrom . ' 00:00:00') : null;
        $dateTo = $request->dateTo ? new Date($request->dateTo . ' 23:59:59') : null;
        $queryIncoming = new GetIncomingListQuery($request->plantationId, $dateFrom, $dateTo);
        $incoming = $this->getIncomingListHandler->handle($queryIncoming);
        $totalIncoming = array_reduce($incoming, function ($result, $item) {
            $result += Money::fromFloat($item->amount)->getAmount();
            return $result;
        }, 0);
        $totalFloatIncoming = $totalIncoming > 0 ? (new Money($totalIncoming))->getAmountAsFloat() : 0;
        $querySpending = new GetSpendingListQuery(null, $request->plantationId, $dateFrom, $dateTo);
        $spending = $this->getSpendingListHandler->handle($querySpending);
        $totalSpending = array_reduce($spending, function ($result, $item) {
            $result += Money::fromFloat($item->amount)->getAmount();
            return $result;
        }, 0);
        $totalFloatSpending = $totalSpending > 0 ? (new Money($totalSpending))->getAmountAsFloat() : 0;
        $profit = ($totalIncoming - $totalSpending) / 100;


        return new GetBudgetResponse(
            $incoming,
            $spending,
            $totalFloatSpending,
            $totalFloatIncoming,
            $profit
        );
    }
}
