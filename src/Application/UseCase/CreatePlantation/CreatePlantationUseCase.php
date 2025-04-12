<?php

namespace App\Application\UseCase\CreatePlantation;

use App\Domain\Factory\PlantationFactoryInterface;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\ValueObject\PlantationName;

class CreatePlantationUseCase
{
    public function __construct(
        private readonly PlantationFactoryInterface $factory,
        private readonly PlantationRepositoryInterface $plantationRepository
    ) {
    }

    public function __invoke(CreatePlantationRequest $request): CreatePlantationResponse
    {
        if ($this->plantationRepository->existsByName($request->name)) {
            throw new \DomainException('Plantation name must be unique.');
        }

        $plantation = $this->factory->create(new PlantationName($request->name));
        $this->plantationRepository->save($plantation);
        return new CreatePlantationResponse($plantation->getId());
    }
}
