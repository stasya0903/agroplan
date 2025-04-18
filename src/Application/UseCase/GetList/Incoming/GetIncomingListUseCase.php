<?php

namespace App\Application\UseCase\GetList\Incoming;

use App\Application\Query\Incoming\GetIncomingListHandler;
use App\Application\Query\Incoming\GetIncomingListQuery;
use App\Application\UseCase\GetList\Incoming\GetIncomingListRequest;
use App\Application\UseCase\GetList\Incoming\GetIncomingListResponse;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use Doctrine\DBAL\Exception;

class GetIncomingListUseCase
{
    public function __construct(
        private readonly GetIncomingListHandler $getNewsHandler,
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(GetIncomingListRequest $request): GetIncomingListResponse
    {
        $dateFrom = $request->dateFrom ? new Date($request->dateFrom . ' 00:00:00') : null;
        $dateTo = $request->dateTo ? new Date($request->dateTo . ' 23:59:59') : null;
        $query = new GetIncomingListQuery(
            $request->plantationId,
            $dateFrom,
            $dateTo,
        );
        $incoming = $this->getNewsHandler->handle($query);
        $total = array_reduce($incoming, function ($result, $item) {
            $result += Money::fromFloat($item->amount)->getAmount();
            return $result;
        }, 0);
        $totalFloat = $total > 0 ? (new Money($total))->getAmountAsFloat() : 0;


        $averagePrice = count($incoming)
            ? array_reduce($incoming, fn($sum, $item) => $sum + $item->price, 0) / count($incoming)
            : 0;

        return new GetIncomingListResponse($incoming, $totalFloat, $averagePrice);
    }
}
