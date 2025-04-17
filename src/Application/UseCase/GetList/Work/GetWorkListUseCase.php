<?php

namespace App\Application\UseCase\GetList\Work;

use App\Application\DTO\PlantationDTO;
use App\Application\DTO\WorkerDTO;
use App\Application\Query\GetWorkListHandler;
use App\Application\Query\GetWorkListQuery;
use App\Application\UseCase\GetList\Plantation\GetPlantationListRequest;
use App\Application\UseCase\GetList\Plantation\GetPlantationListResponse;
use App\Application\UseCase\GetList\Plantation\GetPlantationListUseCase;
use App\Application\UseCase\GetList\Worker\GetWorkerListRequest;
use App\Application\UseCase\GetList\Worker\GetWorkerListResponse;
use App\Domain\Entity\Plantation;
use App\Domain\Entity\Worker;
use App\Domain\Repository\WorkerRepositoryInterface;
use App\Domain\ValueObject\Date;
use App\Infrastructure\Repository\PlantationRepository;

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
