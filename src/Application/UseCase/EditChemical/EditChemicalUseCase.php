<?php

namespace App\Application\UseCase\EditChemical;

use App\Domain\Repository\ChemicalRepositoryInterface;
use App\Domain\ValueObject\Name;

class EditChemicalUseCase
{
    public function __construct(
        private readonly ChemicalRepositoryInterface $chemicalRepository
    ) {
    }

    public function __invoke(EditChemicalRequest $request): EditChemicalResponse
    {
        $chemical = $this->chemicalRepository->find($request->id);

        if (!$chemical) {
            throw new \DomainException('Chemical not found.');
        }

        if (
            $this->chemicalRepository->existsByName($request->commercialName) &&
            $request->commercialName !== $chemical->getCommercialName()->getValue()
        ) {
            throw new \DomainException('Chemical name must be unique.');
        }

        $chemical->setCommercialName(new Name($request->commercialName));
        if ($request->activeIngredient) {
            $chemical->setActiveIngredient(new Name($request->activeIngredient));
        }
        $this->chemicalRepository->save($chemical);

        return new EditChemicalResponse(
            $chemical->getId(),
            $chemical->getCommercialName()->getValue(),
            $chemical->getActiveIngredient()?->getValue() ?? null
        );
    }
}
