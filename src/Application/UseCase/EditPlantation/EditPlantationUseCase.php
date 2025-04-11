<?php

namespace App\Application\UseCase\CreatePlantation;

use App\Domain\Factory\PlantationFactoryInterface;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\ValueObject\PlantationName;

class EditPlantationUseCase
{
    public function __construct(
        private readonly PlantationRepositoryInterface $plantationRepository
    ) {
    }

    public function __invoke(EditPlantationRequest $request): EditPlantationResponse
    {
        $plantation = $this->plantationRepository->find($request->id);

        if (!$plantation) {
            throw new \DomainException('Plantation not found.');
        }

        if (
            $this->plantationRepository->existsByName($request->name) &&
            $request->name !== $plantation->getName()
        ) {
            throw new \DomainException('Plantation name must be unique.');
        }

        $plantation->rename(new PlantationName($request->name));
        $this->plantationRepository->save($plantation);

        return new EditPlantationResponse($plantation->getId(), $plantation->getName());
    }
}
