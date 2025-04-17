<?php

namespace App\Application\UseCase\GetList\Work;

use App\Application\Query\Work\GetWorkListHandler;
use App\Application\Query\Work\GetWorkListQuery;
use App\Domain\ValueObject\Date;

class GetWorkListUseCase
{
    public function __construct(
        private readonly GetWorkListHandler $getNewsHandler,
    ) {
    }

    public function __invoke(GetWorkListRequest $request): iterable
    {
        $dateFrom = $request->dateFrom ? new Date($request->dateFrom . ' 00:00:00') : null;
        $dateTo = $request->dateTo ? new Date($request->dateTo. ' 23:59:59') : null;
        $query = new GetWorkListQuery(
            $request->workTypeId,
            $request->plantationId,
            $dateFrom,
            $dateTo,
        );
        return  $this->getNewsHandler->handle($query);
    }
}
