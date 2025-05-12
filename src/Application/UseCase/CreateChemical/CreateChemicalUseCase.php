<?php

namespace App\Application\UseCase\CreateChemical;

use App\Application\UseCase\CreateIncoming\CreateIncomingRequest;
use App\Application\UseCase\CreateIncoming\CreateIncomingResponse;
use App\Domain\Enums\IncomingTermType;
use App\Domain\Factory\ChemicalFactoryInterface;
use App\Domain\Factory\IncomingFactoryInterface;
use App\Domain\Repository\ChemicalRepositoryInterface;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\IncomingRepositoryInterface;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Note;
use App\Domain\ValueObject\Weight;

class CreateChemicalUseCase
{
    public function __construct(
        private readonly ChemicalFactoryInterface $factory,
        private readonly ChemicalRepositoryInterface $repository
    ) {
    }

    public function __invoke(CreateChemicalRequest $request): CreateChemicalResponse
    {
        if ($this->repository->existsByName($request->commercialName)) {
            throw new \DomainException('Chemical name must be unique.');
        }
        $chemical = $this->factory->create(
            new Name($request->commercialName),
            $request->activeIngredient ? new Name($request->activeIngredient): null
        );
        $this->repository->save($chemical);
        return new CreateChemicalResponse($chemical->getId());
    }
}
