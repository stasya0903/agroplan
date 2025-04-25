<?php

namespace App\Application\UseCase\GetList\SpendingGroup;

use App\Application\Query\SpendingGroup\GetSpendingGroupListHandler;
use App\Application\Query\SpendingGroup\GetSpendingGroupListQuery;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use Doctrine\DBAL\Exception;

class GetSpendingGroupListUseCase
{
    public function __construct(
        private readonly GetSpendingGroupListHandler $getNewsHandler,
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(GetSpendingGroupListRequest $request): GetSpendingGroupListResponse
    {
        $dateFrom = $request->dateFrom ? new Date($request->dateFrom . ' 00:00:00') : null;
        $dateTo = $request->dateTo ? new Date($request->dateTo . ' 23:59:59') : null;
        $query = new GetSpendingGroupListQuery(
            $request->spendingTypeId,
            $request->plantationId,
            $dateFrom,
            $dateTo,
        );
        $groups = $this->getNewsHandler->handle($query);
        $total = array_reduce($groups, function ($result, $group) {
            foreach ($group->spending as $item) {
                $result += Money::fromFloat($item->amount)->getAmount();
            }
            return $result;
        }, 0);
        $totalFloat = $total > 0 ? (new Money($total))->getAmountAsFloat() : 0;
        return new GetSpendingGroupListResponse($groups, $totalFloat);
    }
}
