<?php

namespace App\Application\UseCase\GetList\Worker;

use App\Application\DTO\PlantationDTO;
use App\Application\UseCase\GetList\Plantation\GetPlantationListRequest;
use App\Application\UseCase\GetList\Plantation\GetPlantationListResponse;
use App\Domain\Entity\Plantation;
use App\Infrastructure\Repository\PlantationRepository;

class GetWorkerListUseCase
{
    public function __construct(
        private readonly PlantationRepository $plantationRepository
    ) {
    }

    public function __invoke(GetPlantationListRequest $request): GetPlantationListResponse
    {
        $list = $this->plantationRepository->getList($request->ids ?? []);
        $plantations = array_map(fn(Plantation $plantation) => new PlantationDTO(
            $plantation->getId(),
            $plantation->getName()->getValue()
        ), $list);
        return new GetPlantationListResponse($plantations);
    }
}
