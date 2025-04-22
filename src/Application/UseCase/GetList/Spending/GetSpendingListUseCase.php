<?php

namespace App\Application\UseCase\GetList\Spending;

use App\Application\Query\Spending\GetSpendingListHandler;
use App\Application\Query\Spending\GetSpendingListQuery;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use Doctrine\DBAL\Exception;

class GetSpendingListUseCase
{
    public function __construct(
        private readonly GetSpendingListHandler $getNewsHandler,
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(GetSpendingListRequest $request): GetSpendingListResponse
    {
        $dateFrom = $request->dateFrom ? new Date($request->dateFrom . ' 00:00:00') : null;
        $dateTo = $request->dateTo ? new Date($request->dateTo . ' 23:59:59') : null;
        $query = new GetSpendingListQuery(
            $request->spendingTypeId,
            $request->plantationId,
            $dateFrom,
            $dateTo,
        );
        $spending = $this->getNewsHandler->handle($query);
        $total = array_reduce($spending, function ($result, $item) {
            $result += Money::fromFloat($item->amount)->getAmount();
            return $result;
        }, 0);
        $totalFloat = $total > 0 ? (new Money($total))->getAmountAsFloat() : 0;
        return new GetSpendingListResponse($spending, $totalFloat);
    }
}
