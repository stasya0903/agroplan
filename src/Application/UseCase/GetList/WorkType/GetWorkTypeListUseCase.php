<?php

namespace App\Application\UseCase\GetList\WorkType;

use App\Application\DTO\PlantationDTO;
use App\Application\DTO\WorkTypeDTO;
use App\Application\UseCase\GetList\Plantation\GetPlantationListRequest;
use App\Application\UseCase\GetList\Plantation\GetPlantationListResponse;
use App\Application\UseCase\GetList\WorkType\GetWorkTypeListRequest;
use App\Application\UseCase\GetList\WorkType\GetWorkTypeListResponse;
use App\Domain\Entity\Plantation;
use App\Domain\Entity\WorkType;
use App\Domain\Repository\WorkTypeRepositoryInterface;
use App\Infrastructure\Repository\PlantationRepository;

class GetWorkTypeListUseCase
{
    public function __construct(
        private readonly WorkTypeRepositoryInterface $workTypeRepository
    ) {
    }

    public function __invoke(GetWorkTypeListRequest $request): GetWorkTypeListResponse
    {
        $list = $this->workTypeRepository->getList($request->ids ?? []);
        $workTypes = array_map(fn(WorkType $workType) => new WorkTypeDTO(
            $workType->getId(),
            $workType->getName()->getValue(),
            $workType->isSystem()
        ), $list);
        return new GetWorkTypeListResponse($workTypes);
    }
}
