<?php

namespace App\Application\UseCase\GetList\Worker;

use App\Application\DTO\PlantationDTO;
use App\Application\DTO\WorkerDTO;
use App\Application\UseCase\GetList\Plantation\GetPlantationListRequest;
use App\Application\UseCase\GetList\Plantation\GetPlantationListResponse;
use App\Domain\Entity\Plantation;
use App\Domain\Entity\Worker;
use App\Domain\Repository\WorkerRepositoryInterface;
use App\Infrastructure\Repository\PlantationRepository;

class GetWorkerListUseCase
{
    public function __construct(
        private readonly WorkerRepositoryInterface $workerRepository
    ) {
    }

    public function __invoke(GetWorkerListRequest $request): GetWorkerListResponse
    {
        $list = $this->workerRepository->getList($request->ids ?? []);
        $workers = array_map(fn(Worker $worker) => new WorkerDTO(
            $worker->getId(),
            $worker->getName()->getValue(),
            $worker->getDailyRate()->getAmountAsFloat()
        ), $list);
        return new GetWorkerListResponse($workers);
    }
}
