<?php

namespace App\Application\UseCase\GetList\Plantation;

use App\Application\DTO\PlantationDTO;
use App\Domain\Entity\Plantation;
use App\Infrastructure\Repository\PlantationRepository;

class GetPlantationListUseCase
{
    public function __construct(
        private readonly PlantationRepository $plantationRepository
    ){
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